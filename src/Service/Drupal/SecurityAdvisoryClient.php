<?php

declare(strict_types=1);

namespace App\Service\Drupal;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Fetches a project's security advisories from the drupal.org api-d7.
 */
class SecurityAdvisoryClient
{
    private const string PROJECT_PATH = 'api-d7/node.json';
    private const string PROJECT_TYPE_PREFIX = 'project_';
    private const string SA_TYPE = 'sa';
    private const string ADVISORY_ID_PATTERN = '/SA-(?:CORE|CONTRIB)-\d{4}-\d+/';
    // 50 nodes per page; core has 3 pages of SAs.
    private const int MAX_PAGES = 10;

    private const string NID_CACHE_KEY_PATTERN = 'project_nid.%s';
    private const string SA_CACHE_KEY_PATTERN = 'security_advisories.%s';
    private const int NID_TTL = 30 * 86400;
    private const int NOT_A_PROJECT_TTL = 86400;
    private const int SA_TTL = 12 * 3600;

    public function __construct(
        #[Target('drupal_org.client')]
        private readonly HttpClientInterface $drupalOrgClient,
        #[Target('cache.drupal_org')]
        private readonly CacheInterface $cache,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @return list<SecurityAdvisory> empty when drupal.org knows no such project
     */
    public function fetch(string $project): array
    {
        $nid = $this->findProjectNid($project);
        if (null === $nid) {
            return [];
        }

        return $this->cache->get(sprintf(self::SA_CACHE_KEY_PATTERN, $nid), function (ItemInterface $item) use ($nid): array {
            $item->expiresAfter(self::SA_TTL);

            $advisories = [];
            foreach ($this->nodes(['type' => self::SA_TYPE, 'field_project' => $nid]) as $node) {
                $advisory = $this->parse($node);
                if (null !== $advisory) {
                    $advisories[] = $advisory;
                }
            }

            return $advisories;
        });
    }

    /**
     * Untyped, as projects have several types: core is project_core, not project_module.
     */
    private function findProjectNid(string $project): ?string
    {
        return $this->cache->get(sprintf(self::NID_CACHE_KEY_PATTERN, $project), function (ItemInterface $item) use ($project): ?string {
            $nid = null;
            foreach ($this->page(['field_project_machine_name' => $project])['list'] as $node) {
                if (str_starts_with((string) ($node['type'] ?? ''), self::PROJECT_TYPE_PREFIX) && isset($node['nid'])) {
                    $nid = (string) $node['nid'];
                    break;
                }
            }
            if (null === $nid) {
                $this->logger->warning('drupal.org knows no project {project}', ['project' => $project]);
            }
            $item->expiresAfter(null === $nid ? self::NOT_A_PROJECT_TTL : self::NID_TTL);

            return $nid;
        });
    }

    /**
     * All pages, newest first. The "next" links drupal.org returns lack the
     * .json suffix and answer 404, so pages are requested by number.
     *
     * @param array<string, string> $query
     *
     * @return list<array<string, mixed>>
     */
    private function nodes(array $query): array
    {
        $nodes = [];
        for ($page = 0; $page < self::MAX_PAGES; ++$page) {
            $data = $this->page($query + ['sort' => 'created', 'direction' => 'DESC', 'page' => (string) $page]);
            array_push($nodes, ...$data['list']);
            if (empty($data['next'])) {
                break;
            }
        }

        return $nodes;
    }

    /**
     * @param array<string, string> $query
     *
     * @return array{list: list<array<string, mixed>>, next: mixed}
     */
    private function page(array $query): array
    {
        $data = $this->drupalOrgClient->request('GET', self::PROJECT_PATH, ['query' => $query])->toArray();

        return [
            'list' => array_values(array_filter($data['list'] ?? [], is_array(...))),
            'next' => $data['next'] ?? null,
        ];
    }

    /**
     * @param array<string, mixed> $node
     */
    private function parse(array $node): ?SecurityAdvisory
    {
        $title = (string) ($node['title'] ?? '');
        // PSAs and other nodes without an advisory id are skipped.
        if (!preg_match(self::ADVISORY_ID_PATTERN, $title, $matches)) {
            return null;
        }
        $cves = $node['field_sa_cve'] ?? [];

        return new SecurityAdvisory(
            $matches[0],
            (string) ($node['url'] ?? ''),
            is_array($cves) && isset($cves[0]) ? (string) $cves[0] : null,
            $title,
            (string) ($node['field_affected_versions'] ?? ''),
            new \DateTimeImmutable('@'.(int) ($node['created'] ?? 0)),
        );
    }
}
