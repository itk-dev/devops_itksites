<?php

declare(strict_types=1);

namespace App\Service\Drupal;

use App\Utils\DrupalVersion;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Fetches release-history from updates.drupal.org.
 */
class ReleaseHistoryClient
{
    private const string RELEASE_HISTORY_PATH_PATTERN = 'release-history/%s/current';
    private const string CACHE_KEY_PATTERN = 'release_history.%s';
    private const int TTL = 12 * 3600;
    private const int NOT_A_PROJECT_TTL = 7 * 86400;

    private const string TERM_SECURITY_UPDATE = 'Security update';
    private const string TERM_INSECURE = 'Insecure';

    public function __construct(
        #[Target('drupal_updates.client')]
        private readonly HttpClientInterface $drupalUpdatesClient,
        #[Target('cache.drupal_org')]
        private readonly CacheInterface $cache,
    ) {
    }

    /**
     * @return ReleaseHistory|null null when drupal.org knows no such project
     */
    public function fetch(string $project): ?ReleaseHistory
    {
        return $this->cache->get(sprintf(self::CACHE_KEY_PATTERN, $project), function (ItemInterface $item) use ($project): ?ReleaseHistory {
            $body = $this->drupalUpdatesClient->request('GET', sprintf(self::RELEASE_HISTORY_PATH_PATTERN, $project))->getContent();
            $history = $this->parse($body);
            $item->expiresAfter(null === $history ? self::NOT_A_PROJECT_TTL : self::TTL);

            return $history;
        });
    }

    private function parse(string $body): ?ReleaseHistory
    {
        $xml = simplexml_load_string($body, options: LIBXML_NOERROR | LIBXML_NOWARNING);
        if (false === $xml) {
            throw new \UnexpectedValueException('Invalid release-history XML');
        }
        // Non-projects are answered with HTTP 200 and an <error> body.
        if ('project' !== $xml->getName()) {
            return null;
        }

        $releases = [];
        foreach ($xml->xpath('releases/release') ?: [] as $release) {
            $version = DrupalVersion::toComposer((string) $release->version);
            if (null === $version) {
                continue;
            }
            $terms = array_map(strval(...), $release->xpath('terms/term/value') ?: []);
            $releases[$version] = new Release(
                $version,
                $terms,
                in_array(self::TERM_SECURITY_UPDATE, $terms, true),
                in_array(self::TERM_INSECURE, $terms, true),
            );
        }

        return new ReleaseHistory($releases);
    }
}
