<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Minimal JSON-RPC 2.0 client for the Leantime API.
 *
 * Exposes the calls this app needs: listing open "Sikkerhedsopdatering"
 * tickets, mapping codeowner emails to user IDs, and creating security
 * tickets. The scoped HTTP client `$leantimeClient` supplies the base URI and
 * the x-api-key header; this class only assembles request bodies and unwraps
 * responses.
 *
 * Two APIs sit behind that one host. Tickets are read and written over
 * JSON-RPC, because only it can create one. The user directory comes from the
 * ITK data-api plugin instead: `users.getAll` answers
 * `-32001 You are not allowed to perform this action` for our API key, while
 * the plugin serves the same id/name/email over a key we already hold.
 *
 * @see https://github.com/ITK-Leantime/data-api
 */
class LeantimeService
{
    private const string JSONRPC_VERSION = '2.0';
    private const string API_PATH = '/api/jsonrpc/';
    private const string DATE_FORMAT = 'Y-m-d';

    /**
     * The data-api plugin's path. Case-sensitive — the lowercase spelling in
     * the plugin's own README answers 404.
     */
    private const string DATA_API_PATH = '/APIData/API/';

    /** Well under the plugin's cap of 1000, which it silently applies. */
    private const int DATA_API_PAGE_SIZE = 500;

    private const string TICKET_STATUS_NEW = '3';
    private const string TICKET_PRIORITY_CRITICAL = '1';
    private const string TICKET_TYPE_TASK = 'task';
    private const string SECURITY_TICKET_TITLE = 'Sikkerhedsopdatering';

    /**
     * Leantime status IDs treated as "open". Tune per Leantime instance.
     * Excludes 0 = Done.
     *
     * @var list<string>
     */
    private const array OPEN_STATUS_IDS = ['1', '2', '3', '4'];

    private const float DEFAULT_HOURS = 1.0;

    /**
     * @var array<int, string>|null cached display-name lookup keyed by user id
     */
    private ?array $userNamesById = null;

    /**
     * @var array<string, int>|null cached email → user id lookup (lowercase email)
     */
    private ?array $userIdsByEmail = null;

    public function __construct(
        private readonly HttpClientInterface $leantimeClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Find currently-open security tickets across all Leantime projects.
     *
     * Pre-filters via the Leantime `searchCriteria` (term + type + status),
     * tightens the LIKE match into an exact headline check, and keeps the
     * most recent matching ticket per Leantime project id.
     *
     * @return array<int, array{assigneeName: ?string, createdAt: ?string, id: int}> tickets keyed by Leantime project id
     *
     * @throws \RuntimeException if the Leantime API rejects the request or the transport fails
     */
    public function findOpenSecurityTickets(): array
    {
        $result = $this->request('leantime.rpc.tickets.getAll', [
            'searchCriteria' => [
                'term' => self::SECURITY_TICKET_TITLE,
                'type' => self::TICKET_TYPE_TASK,
                // Leantime wants one scalar here. Handed the list as an array
                // it answers -32000 Server error, which is what made this page
                // show nothing but a flash.
                'status' => implode(',', self::OPEN_STATUS_IDS),
            ],
        ]);

        if (!is_array($result)) {
            return [];
        }

        $needle = mb_strtolower(self::SECURITY_TICKET_TITLE);
        $exact = [];
        foreach ($result as $ticket) {
            if (!is_array($ticket) || !isset($ticket['projectId'])) {
                continue;
            }
            $headline = isset($ticket['headline']) ? mb_strtolower(trim((string) $ticket['headline'])) : '';
            if ($headline !== $needle) {
                continue;
            }
            $exact[] = $ticket;
        }

        usort(
            $exact,
            static fn (array $a, array $b): int => strcmp((string) ($b['dateCreated'] ?? ''), (string) ($a['dateCreated'] ?? '')),
        );

        $byProjectId = [];
        foreach ($exact as $ticket) {
            $projectId = (int) $ticket['projectId'];
            if (isset($byProjectId[$projectId])) {
                continue;
            }
            $byProjectId[$projectId] = [
                'id' => (int) ($ticket['id'] ?? 0),
                'assigneeName' => $this->resolveUserName($ticket['editorId'] ?? null),
                'createdAt' => isset($ticket['dateCreated']) ? (string) $ticket['dateCreated'] : null,
            ];
        }

        return $byProjectId;
    }

    /**
     * Resolve a Leantime user id for an email address.
     *
     * Lazy-loads the Leantime user directory on first call and looks the
     * email up case-insensitively. Returns null when the email is empty or
     * unknown so the caller can fall back to an unassigned ticket.
     *
     * @param string $email free-form email — leading/trailing whitespace and case are normalized
     *
     * @return int|null the Leantime user id, or null when no match exists
     *
     * @throws \RuntimeException if the user directory fetch fails
     */
    public function findUserIdByEmail(string $email): ?int
    {
        $email = mb_strtolower(trim($email));
        if ('' === $email) {
            return null;
        }
        $this->loadUsers();

        return $this->userIdsByEmail[$email] ?? null;
    }

    /**
     * Create a "Sikkerhedsopdatering" task in the given Leantime project.
     *
     * Submits a ticket with priority `critical`, status `new`, a one-hour
     * planned estimate, and editFrom/editTo/dateToFinish all set to today.
     * A null `$userId` produces an unassigned ticket.
     *
     * @param int      $projectId Leantime project id the ticket belongs to
     * @param int|null $userId    Leantime user id to assign the ticket to, or null for unassigned
     *
     * @return int the new ticket's Leantime id, or 0 if Leantime returned an unexpected response shape
     *
     * @throws \RuntimeException if the API rejects the request or the transport fails
     */
    public function createSecurityTicket(int $projectId, ?int $userId = null): int
    {
        $date = date(self::DATE_FORMAT);

        $result = $this->request('leantime.rpc.tickets.addTicket', [
            'values' => [
                'headline' => self::SECURITY_TICKET_TITLE,
                'description' => '',
                'projectId' => $projectId,
                'type' => self::TICKET_TYPE_TASK,
                'status' => self::TICKET_STATUS_NEW,
                'priority' => self::TICKET_PRIORITY_CRITICAL,
                'dateToFinish' => $date,
                'editFrom' => $date,
                'editTo' => $date,
                'planHours' => (string) self::DEFAULT_HOURS,
                'hourRemaining' => (string) self::DEFAULT_HOURS,
                'tags' => '',
                'milestoneid' => '',
                'editorId' => $userId ?? '',
            ],
        ]);

        if (is_array($result) && isset($result[0])) {
            return (int) $result[0];
        }

        return is_numeric($result) ? (int) $result : 0;
    }

    /**
     * Send a JSON-RPC 2.0 request to the Leantime API.
     *
     * Wraps the call in the JSON-RPC envelope, decodes the response, and
     * normalizes both transport failures and API-level error objects into
     * RuntimeException. Every failure is logged first: the callers turn the
     * exception into a flash message and nothing else records it, so without
     * this the only trace of a broken integration is whoever happened to open
     * the admin page.
     *
     * @param string               $method JSON-RPC method name (e.g. `leantime.rpc.tickets.getAll`)
     * @param array<string, mixed> $params method parameters to forward verbatim to Leantime
     *
     * @return mixed the decoded `result` field from the JSON-RPC response, or null when absent
     *
     * @throws \RuntimeException on transport error or when the API responds with an `error` object
     */
    private function request(string $method, array $params = []): mixed
    {
        try {
            $response = $this->leantimeClient->request('POST', self::API_PATH, [
                'json' => [
                    'jsonrpc' => self::JSONRPC_VERSION,
                    'method' => $method,
                    'params' => $params,
                    'id' => uniqid('', true),
                ],
            ]);
            $data = $response->toArray(false);
        } catch (ExceptionInterface $e) {
            $this->logger->error('Leantime request failed', [
                'method' => $method,
                'exception' => $e,
            ]);

            throw new \RuntimeException('Leantime request failed: '.$e->getMessage(), 0, $e);
        }

        if (isset($data['error'])) {
            // The spec says `error` is an object, but a malformed one would
            // otherwise turn an API error into a fatal on the string offset.
            $error = is_array($data['error']) ? $data['error'] : ['message' => $data['error']];

            $this->logger->error('Leantime API error', [
                'method' => $method,
                'params' => $params,
                'code' => $error['code'] ?? null,
                'message' => $error['message'] ?? null,
                // Leantime puts the underlying exception detail in `data`. It is
                // the only thing separating a rejected API key from an unknown
                // method or a param shape this instance does not accept, so it
                // is logged even though the flash message stays short.
                'data' => $error['data'] ?? null,
            ]);

            throw new \RuntimeException(sprintf('Leantime API error (%s): %s', $error['code'] ?? '?', $error['message'] ?? 'Unknown error'));
        }

        return $data['result'] ?? null;
    }

    /**
     * Look up a Leantime user's display name by id.
     *
     * Lazy-loads the user directory on first call and coerces the id to int
     * (Leantime delivers it as either string or int). Returns null when the
     * id is empty or unknown.
     *
     * @param mixed $userId raw user id from the Leantime payload (int, numeric string, or null)
     *
     * @return string|null the user's display name, or null when no match exists
     *
     * @throws \RuntimeException if the user directory fetch fails
     */
    private function resolveUserName(mixed $userId): ?string
    {
        if (null === $userId || '' === $userId) {
            return null;
        }
        $this->loadUsers();

        return $this->userNamesById[(int) $userId] ?? null;
    }

    /**
     * Populate the user id/name/email caches from the data-api plugin.
     *
     * Idempotent — fetches the directory at most once per service instance,
     * paging until a short page arrives. Builds an id → name map and an
     * email → id map keyed by lowercase email; entries with no id are skipped.
     *
     * @throws \RuntimeException if the request fails or the plugin rejects it
     */
    private function loadUsers(): void
    {
        if (null !== $this->userNamesById) {
            return;
        }

        $this->userNamesById = [];
        $this->userIdsByEmail = [];

        $start = 0;
        do {
            $page = $this->requestData('workers', ['start' => $start, 'limit' => self::DATA_API_PAGE_SIZE]);
            $results = $page['results'];

            foreach ($results as $user) {
                if (!isset($user['id'])) {
                    continue;
                }
                $id = (int) $user['id'];
                // The endpoint pages on id, ascending, so the next page starts
                // above the highest one seen.
                $start = max($start, $id + 1);

                $this->userNamesById[$id] = '' !== trim((string) ($user['name'] ?? ''))
                    ? trim((string) $user['name'])
                    : 'Unknown';

                $email = mb_strtolower(trim((string) ($user['email'] ?? '')));
                if ('' !== $email) {
                    $this->userIdsByEmail[$email] = $id;
                }
            }

            // Against the limit the plugin actually applied, not the one we
            // asked for: it caps silently, and comparing to our own number
            // would stop the paging after the first page.
            $applied = (int) ($page['parameters']['limit'] ?? self::DATA_API_PAGE_SIZE);
        } while ([] !== $results && count($results) >= $applied);
    }

    /**
     * Fetch one page from the data-api plugin.
     *
     * @param string               $type   plugin resource, e.g. `workers`
     * @param array<string, mixed> $params `start`, `limit` and the plugin's other filters
     *
     * @return array{results: list<array<string, mixed>>, parameters: array<string, mixed>}
     *
     * @throws \RuntimeException on transport error, a non-2xx response, or a malformed body
     */
    private function requestData(string $type, array $params): array
    {
        try {
            $response = $this->leantimeClient->request('POST', self::DATA_API_PATH.$type, [
                'headers' => ['Accept' => 'application/json'],
                'json' => $params,
            ]);
            $statusCode = $response->getStatusCode();
            $data = $response->toArray(false);
        } catch (ExceptionInterface $e) {
            $this->logger->error('Leantime data-api request failed', [
                'type' => $type,
                'exception' => $e,
            ]);

            throw new \RuntimeException('Leantime data-api request failed: '.$e->getMessage(), 0, $e);
        }

        if (200 !== $statusCode || !isset($data['results']) || !is_array($data['results'])) {
            $this->logger->error('Leantime data-api error', [
                'type' => $type,
                'params' => $params,
                'status_code' => $statusCode,
                // The plugin answers 400 with an `error` describing the
                // parameter it could not read.
                'error' => $data['error'] ?? null,
            ]);

            throw new \RuntimeException(sprintf('Leantime data-api error (%d): %s', $statusCode, $data['error'] ?? 'Unexpected response'));
        }

        return [
            'results' => array_values(array_filter($data['results'], is_array(...))),
            'parameters' => is_array($data['parameters'] ?? null) ? $data['parameters'] : [],
        ];
    }
}
