<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Entity\DetectionResult;
use App\Entity\Server;
use App\Types\DetectionType;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\ApplicationTester;

class PurgeCommandTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private const string ROOT_DIR = '/data/www/purge_probe/htdocs';

    public function testPurgesResultsOlderThanARelativeDate(): void
    {
        self::bootKernel();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $server = $entityManager->getRepository(Server::class)->findOneBy([]);
        foreach (['-31 days' => 'old', '-29 days' => 'recent'] as $lastContact => $data) {
            $result = new DetectionResult();
            $result->setType(DetectionType::DOCKER);
            $result->setRootDir(self::ROOT_DIR);
            $result->setServer($server);
            $result->setData($data);
            $result->generateHash();
            $result->setLastContact(new \DateTimeImmutable($lastContact));
            $entityManager->persist($result);
        }
        $entityManager->flush();

        $tester = $this->console(['--date' => '30 days ago']);

        $tester->assertCommandIsSuccessful();
        $entityManager->clear();
        $remaining = $entityManager->getRepository(DetectionResult::class)->findBy(['rootDir' => self::ROOT_DIR]);
        $this->assertSame(['recent'], array_map(static fn (DetectionResult $result): string => $result->getData(), $remaining));
    }

    public function testRejectsAnUnparsableDate(): void
    {
        self::bootKernel();

        $this->console(['--date' => 'not a date'])->assertCommandFailed();
    }

    public function testRunsDailyOnTheDefaultSchedule(): void
    {
        self::bootKernel();

        $tester = $this->console(['command' => 'debug:scheduler', 'schedule' => ['default']]);

        $tester->assertCommandIsSuccessful();
        $this->assertStringContainsString('every 1 day with 0-1800 second jitter', $tester->getDisplay());
        $this->assertStringContainsString('app:data:purge --date="30 days ago"', $tester->getDisplay());
    }

    /**
     * @param array<string, mixed> $input
     */
    private function console(array $input): ApplicationTester
    {
        $application = new Application(self::$kernel);
        $application->setAutoExit(false);

        $tester = new ApplicationTester($application);
        $tester->run($input + ['command' => 'app:data:purge']);

        return $tester;
    }
}
