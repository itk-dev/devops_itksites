<?php

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\ApplicationTester;

/**
 * Tests for UserSetApiKeyCommand.
 *
 * @see UserSetApiKeyCommand
 */
class UserSetApiKeyCommandTest extends KernelTestCase
{
    public function testUserSetApiKey(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);
        // this is key: don't terminate the PHP process after running the command
        $application->setAutoExit(false);

        $applicationTester = new ApplicationTester($application);
        $applicationTester->run([
            'command' => 'app:user:set-api-key',
            '--help' => true,
        ]);
        $applicationTester->assertCommandIsSuccessful();

        // Missing user ID argument.
        $applicationTester->run([
            'command' => 'app:user:set-api-key',
        ]);
        $applicationTester->assertCommandFailed();

        // Invalid user ID argument.
        $applicationTester->run([
            'command' => 'app:user:set-api-key',
            'user-id' => 'this-user-does-no-exist',
        ]);
        $applicationTester->assertCommandIsInvalid();
        $output = $applicationTester->getDisplay();
        $this->assertStringContainsString('Cannot load user with id this-user-does-no-exist', $output);

        // Valid user ID (name).
        $applicationTester->run([
            'command' => 'app:user:set-api-key',
            'user-id' => 'admin',
            '--no-interaction' => true,
        ]);
        $applicationTester->assertCommandIsSuccessful();

        $output = $applicationTester->getDisplay();
        $this->assertStringContainsString('API key for user admin@example.com set to', $output);

        // Valid user ID (email).
        $applicationTester->run([
            'command' => 'app:user:set-api-key',
            'user-id' => 'admin@example.com',
            '--no-interaction' => true,
        ]);
        $applicationTester->assertCommandIsSuccessful();

        $output = $applicationTester->getDisplay();
        $this->assertStringContainsString('API key for user admin@example.com set to', $output);
    }
}
