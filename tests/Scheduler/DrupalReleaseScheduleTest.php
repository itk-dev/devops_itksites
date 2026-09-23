<?php

declare(strict_types=1);

namespace App\Tests\Scheduler;

use App\Message\CheckDrupalReleases;
use App\Scheduler\DrupalReleaseSchedule;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Scheduler\Generator\MessageContext;

class DrupalReleaseScheduleTest extends KernelTestCase
{
    public function testScheduleIsRegisteredAsTransport(): void
    {
        self::bootKernel();

        $this->assertTrue(self::getContainer()->get('messenger.receiver_locator')->has('scheduler_drupal_releases'));
    }

    public function testRecursEvery12HoursFrom0600AndIsStateful(): void
    {
        self::bootKernel();
        $schedule = self::getContainer()->get(DrupalReleaseSchedule::class)->getSchedule();

        $this->assertNotNull($schedule->getState());

        $messages = $schedule->getRecurringMessages();
        $this->assertCount(1, $messages);
        $message = $messages[0];
        $trigger = $message->getTrigger();

        $context = new MessageContext('drupal_releases', $message->getId(), $trigger, new \DateTimeImmutable());
        $this->assertInstanceOf(CheckDrupalReleases::class, iterator_to_array($message->getMessages($context))[0]);

        $this->assertEquals(new \DateTimeImmutable('18:00'), $trigger->getNextRunDate(new \DateTimeImmutable('07:00')));
        $this->assertEquals(new \DateTimeImmutable('tomorrow 06:00'), $trigger->getNextRunDate(new \DateTimeImmutable('19:00')));
    }
}
