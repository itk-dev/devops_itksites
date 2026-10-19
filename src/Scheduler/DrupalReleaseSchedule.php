<?php

declare(strict_types=1);

namespace App\Scheduler;

use App\Message\CheckDrupalReleases;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Check drupal.org releases every 12 hours.
 *
 * Anchored and stateful because the worker restarts every 15 minutes
 * (--time-limit=900), which would otherwise reset the clock on each boot.
 */
#[AsSchedule('drupal_releases')]
final readonly class DrupalReleaseSchedule implements ScheduleProviderInterface
{
    public function __construct(
        private CacheInterface $cache,
    ) {
    }

    public function getSchedule(): Schedule
    {
        return new Schedule()
            ->add(RecurringMessage::every('12 hours', new CheckDrupalReleases(), from: '06:00'))
            ->stateful($this->cache);
    }
}
