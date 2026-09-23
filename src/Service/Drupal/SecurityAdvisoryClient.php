<?php

declare(strict_types=1);

namespace App\Service\Drupal;

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
    private const string PROJECT_TYPE = 'project_module';
    private const string SA_TYPE = 'sa';
    private const string ADVISORY_ID_PATTERN = '/SA-(?:CORE|CONTRIB)-\d{4}-\d+/';

    private const string NID_CACHE_KEY_PATTERN = 'project_nid.%s';
    private const string SA_CACHE_KEY_PATTERN = 'security_advisories.%s';
    private const int NID_TTL = 30 * 86400;
    private const int NOT_A_PROJECT_TTL = 7 * 86400;
    private const int SA_TTL = 12 * 3600;

    public function __construct(
        #[Target('drupal_org.client')]
        private readonly HttpClientInterface $drupalOrgClient,
        #[Target('cache.drupal_org')]
        private readonly CacheInterface $cache,
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

    private function findProjectNid(string $project): ?string
    {
        return $this->cache->get(sprintf(self::NID_CACHE_KEY_PATTERN, $project), function (ItemInterface $item) use ($project): ?string {
            $nodes = $this->nodes(['type' => self::PROJECT_TYPE, 'field_project_machine_name' => $project]);
            $nid = isset($nodes[0]['nid']) ? (string) $nodes[0]['nid'] : null;
            $item->expiresAfter(null === $nid ? self::NOT_A_PROJECT_TTL : self::NID_TTL);

            return $nid;
        });
    }

    /**
     * @param array<string, string> $query
     *
     * @return list<array<string, mixed>>
     */
    private function nodes(array $query): array
    {
        // ponytail: first page only; follow "next" if a project outgrows one page of SAs.
        $data = $this->drupalOrgClient->request('GET', self::PROJECT_PATH, ['query' => $query])->toArray();

        return array_values(array_filter($data['list'] ?? [], is_array(...)));
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
