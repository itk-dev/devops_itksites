<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\AbstractBaseEntity;
use App\Entity\Installation;
use App\Entity\Module;
use App\Entity\ModuleVersion;
use App\Repository\ModuleRepository;
use App\Repository\ModuleVersionRepository;
use App\Service\ModuleVersionFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

/**
 * Same contract as PackageVersionFactoryTest: deduplicate within a call, carry
 * nothing between calls.
 */
#[CoversClass(ModuleVersionFactory::class)]
class ModuleVersionFactoryTest extends TestCase
{
    /** @var list<object> */
    private array $persisted = [];

    protected function setUp(): void
    {
        $this->persisted = [];
    }

    public function testRepeatedModuleIsCreatedOnce(): void
    {
        // Drupal delivers modules as an object keyed by machine name, so a
        // repeat within one payload means the same key twice — which only the
        // package differing can produce.
        $this->factory()->setModuleVersions(new Installation(), (object) [
            'views' => self::installed('drupal/views', '1.0.0'),
        ]);

        $this->assertCount(1, $this->modules());
        $this->assertCount(1, $this->moduleVersions());
    }

    public function testSameModuleWithTwoVersionsCreatesTwoVersions(): void
    {
        $factory = $this->factory();

        $factory->setModuleVersions(new Installation(), (object) [
            'views' => self::installed('drupal/views', '1.0.0'),
        ]);
        $modulesAfterFirst = count($this->modules());

        $factory->setModuleVersions(new Installation(), (object) [
            'views' => self::installed('drupal/views', '2.0.0'),
        ]);

        $this->assertSame(1, $modulesAfterFirst);
        $this->assertCount(2, $this->moduleVersions(), 'distinct versions are distinct rows');
    }

    public function testTwoNewModulesSharingAVersionStringStayApart(): void
    {
        $this->factory()->setModuleVersions(new Installation(), (object) [
            'views' => self::installed('drupal/views', '1.0.0'),
            'token' => self::installed('drupal/token', '1.0.0'),
        ]);

        $this->assertCount(2, $this->modules());
        $this->assertCount(2, $this->moduleVersions(), 'a shared version string must not merge two modules');
    }

    public function testNothingCarriesOverBetweenCalls(): void
    {
        $factory = $this->factory();

        $factory->setModuleVersions(new Installation(), (object) ['views' => self::installed('drupal/views', '1.0.0')]);
        $factory->setModuleVersions(new Installation(), (object) ['views' => self::installed('drupal/views', '1.0.0')]);

        $modules = array_values($this->modules());
        $this->assertCount(2, $modules, 'each call starts from an empty buffer');
        $this->assertNotSame($modules[0], $modules[1]);
    }

    public function testAFailedFlushLeavesNothingForTheNextCall(): void
    {
        $factory = $this->factory(throwOnFirstFlush: true);

        try {
            $factory->setModuleVersions(new Installation(), (object) ['views' => self::installed('drupal/views', '1.0.0')]);
            $this->fail('expected the failing flush to throw');
        } catch (\RuntimeException) {
            // Expected.
        }

        $this->persisted = [];
        $factory->setModuleVersions(new Installation(), (object) ['views' => self::installed('drupal/views', '1.0.0')]);

        $this->assertCount(1, $this->modules(), 'the module is created afresh, not taken from a stale buffer');
    }

    /**
     * ModuleVersion::getVersion() reports 'Unknown' for a null version, so the
     * scan this replaced never matched a null-versioned module in the buffer and
     * created a row per occurrence. That quirk is preserved deliberately —
     * changing it would change which rows get written.
     */
    public function testNullVersionsAreNotDeduplicated(): void
    {
        $this->factory()->setModuleVersions(new Installation(), (object) [
            'views' => self::installed('drupal/views', null),
            'token' => self::installed('drupal/views', null),
        ]);

        $this->assertCount(2, $this->moduleVersions(), 'documented pre-existing behaviour, not an endorsement');
    }

    private function factory(bool $throwOnFirstFlush = false): ModuleVersionFactory
    {
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('persist')->willReturnCallback(
            function (object $entity): void {
                // Doctrine assigns the ULID during persist(), because
                // UlidGenerator is a CUSTOM rather than a post-insert
                // generator. The stub does the same, so entities here are in
                // the state the factory actually meets them in.
                if ($entity instanceof AbstractBaseEntity) {
                    $entity->setId(new Ulid());
                }

                $this->persisted[] = $entity;
            }
        );

        $flushes = 0;
        $entityManager->method('flush')->willReturnCallback(
            function () use ($throwOnFirstFlush, &$flushes): void {
                if ($throwOnFirstFlush && 0 === $flushes++) {
                    throw new \RuntimeException('flush failed');
                }
            }
        );

        $moduleRepository = $this->createStub(ModuleRepository::class);
        $moduleRepository->method('findOneBy')->willReturn(null);
        $moduleVersionRepository = $this->createStub(ModuleVersionRepository::class);
        $moduleVersionRepository->method('findOneBy')->willReturn(null);

        return new ModuleVersionFactory($entityManager, $moduleRepository, $moduleVersionRepository);
    }

    private static function installed(string $package, string|int|float|null $version): object
    {
        return (object) [
            'package' => $package,
            'version' => $version,
            'status' => 'Enabled',
        ];
    }

    /** @return array<int, Module> */
    private function modules(): array
    {
        return array_filter($this->persisted, static fn (object $e): bool => $e instanceof Module);
    }

    /** @return array<int, ModuleVersion> */
    private function moduleVersions(): array
    {
        return array_filter($this->persisted, static fn (object $e): bool => $e instanceof ModuleVersion);
    }
}
