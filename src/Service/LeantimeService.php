<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Minimal JSON-RPC 2.0 client for the Leantime API.
 *
 * Exposes the calls this app needs: listing open "Sikkerhedsopdatering"
 * tickets, mapping codeowner emails to user IDs, and creating security
 * tickets. The scoped HTTP client `$leantimeClient` supplies the base URI and
 * the x-api-key header; this class only assembles the JSON-RPC body and
 * unwraps responses.
 */
class LeantimeService
{
    private const string JSONRPC_VERSION = '2.0';
    private const string API_PATH = '/api/jsonrpc/';
    private const string DATE_FORMAT = 'Y-m-d';

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
                'status' => self::OPEN_STATUS_IDS,
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
     * RuntimeException.
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
            throw new \RuntimeException('Leantime request failed: '.$e->getMessage(), 0, $e);
        }

        if (isset($data['error'])) {
            throw new \RuntimeException(sprintf('Leantime API error (%s): %s', $data['error']['code'] ?? '?', $data['error']['message'] ?? 'Unknown error'));
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
     * Populate the user id/name/email caches from Leantime.
     *
     * Idempotent — fetches the directory at most once per service instance.
     * Builds an id → display-name map (firstname+lastname, falling back to
     * username) and an email → id map keyed by lowercase email; entries with
     * no id are skipped.
     *
     * @throws \RuntimeException if the Leantime API rejects the request or the transport fails
     */
    private function loadUsers(): void
    {
        if (null !== $this->userNamesById) {
            return;
        }

        $this->userNamesById = [];
        $this->userIdsByEmail = [];

        $result = $this->request('leantime.rpc.users.getAll');
        if (!is_array($result)) {
            return;
        }

        foreach ($result as $user) {
            if (!is_array($user) || !isset($user['id'])) {
                continue;
            }
            $id = (int) $user['id'];

            $name = trim(((string) ($user['firstname'] ?? '')).' '.((string) ($user['lastname'] ?? '')));
            if ('' === $name) {
                $name = (string) ($user['username'] ?? 'Unknown');
            }
            $this->userNamesById[$id] = $name;

            $email = mb_strtolower(trim((string) ($user['email'] ?? '')));
            if ('' !== $email) {
                $this->userIdsByEmail[$email] = $id;
            }
        }
    }
}
