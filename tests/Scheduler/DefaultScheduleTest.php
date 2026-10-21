<?php

declare(strict_types=1);

namespace App\Tests\Scheduler;

use App\Message\CheckDrupalReleases;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Messenger\RunCommandMessage;
use Symfony\Component\Scheduler\Generator\MessageContext;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;

class DefaultScheduleTest extends KernelTestCase
{
    public function testDefaultIsTheOnlyScheduleTransport(): void
    {
        self::bootKernel();
        $receivers = self::getContainer()->get('messenger.receiver_locator');

        $this->assertTrue($receivers->has('scheduler_default'));
        $this->assertFalse($receivers->has('scheduler_drupal_releases'));
    }

    public function testIsStatefulAndHoldsTheDrupalCheckAndThePurge(): void
    {
        self::bootKernel();
        /** @var Schedule $schedule */
        $schedule = self::getContainer()->get('scheduler.provider.default');

        // Stateful, so the worker's 15 minute restarts do not skip runs.
        $this->assertNotNull($schedule->getState());

        $messages = [];
        foreach ($schedule->getRecurringMessages() as $message) {
            $messages[$this->messageOf($message)::class] = $message;
        }
        $this->assertEqualsCanonicalizing([CheckDrupalReleases::class, RunCommandMessage::class], array_keys($messages));

        $trigger = $messages[CheckDrupalReleases::class]->getTrigger();
        $this->assertEquals(new \DateTimeImmutable('18:00'), $trigger->getNextRunDate(new \DateTimeImmutable('07:00')));
        $this->assertEquals(new \DateTimeImmutable('tomorrow 06:00'), $trigger->getNextRunDate(new \DateTimeImmutable('19:00')));
    }

    private function messageOf(RecurringMessage $message): object
    {
        $context = new MessageContext('default', $message->getId(), $message->getTrigger(), new \DateTimeImmutable());

        return iterator_to_array($message->getMessages($context))[0];
    }
}
