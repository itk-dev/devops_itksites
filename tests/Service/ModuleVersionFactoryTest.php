<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Installation;
use App\Entity\Module;
use App\Service\ModuleVersionFactory;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Runs the real factory against the test database.
 */
class ModuleVersionFactoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private EntityManagerInterface $entityManager;
    private Installation $installation;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->installation = $this->entityManager->getRepository(Installation::class)->findOneBy([]);
    }

    public function testSetsProjectFromDrush(): void
    {
        $this->modules(['webform_ui' => ['project' => 'webform', 'path' => 'modules/contrib/webform/modules/webform_ui']]);

        $this->assertSame('webform', $this->module('webform_ui')->getProject());
    }

    public function testDerivesDrupalForCoreModules(): void
    {
        $this->modules(['text' => ['package' => 'Field types', 'project' => '', 'path' => 'core/modules/text']]);

        $this->assertSame('drupal', $this->module('text')->getProject());
    }

    public function testLeavesProjectNullWhenUnknown(): void
    {
        $this->modules([
            'os2forms' => ['project' => '', 'path' => 'modules/contrib/os2forms'],
            'legacy_probe' => [],
        ]);

        $this->assertNull($this->module('os2forms')->getProject());
        $this->assertNull($this->module('legacy_probe')->getProject());
    }

    /**
     * Replayed results must fill in the project on modules stored before it was.
     */
    public function testSetsProjectOnExistingModule(): void
    {
        $this->modules(['node' => []]);
        $this->modules(['node' => ['project' => '', 'path' => 'core/modules/node']]);

        $this->assertSame('drupal', $this->module('node')->getProject());
    }

    /**
     * @param array<string, array<string, string>> $modules drush fields per module name
     */
    private function modules(array $modules): void
    {
        $installed = [];
        foreach ($modules as $name => $fields) {
            $installed[$name] = (object) ($fields + ['package' => 'Probe', 'status' => 'Enabled', 'version' => '1.0.0']);
        }

        self::getContainer()->get(ModuleVersionFactory::class)->setModuleVersions($this->installation, (object) $installed);
    }

    private function module(string $name): Module
    {
        $module = $this->entityManager->getRepository(Module::class)->findOneBy(['name' => $name]);
        self::assertNotNull($module);

        return $module;
    }
}
