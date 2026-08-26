<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\AbstractBaseEntity;
use App\Entity\Installation;
use App\Entity\Package;
use App\Entity\PackageVersion;
use App\Repository\PackageRepository;
use App\Repository\PackageVersionRepository;
use App\Service\PackageVersionFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

/**
 * The factory has to deduplicate against entities it has persisted but not yet
 * flushed, because the repositories cannot see those yet. It used to keep those
 * buffers in properties, which meant they outlived the call. These tests pin
 * down both halves: deduplication still works inside a call, and nothing
 * carries over between calls.
 */
#[CoversClass(PackageVersionFactory::class)]
class PackageVersionFactoryTest extends TestCase
{
    /** @var list<object> */
    private array $persisted = [];

    protected function setUp(): void
    {
        $this->persisted = [];
    }

    public function testRepeatedPackageIsCreatedOnce(): void
    {
        $this->factory()->setPackageVersions(new Installation(), [
            self::installed('acme/foo', '1.0.0'),
            self::installed('acme/foo', '1.0.0'),
        ]);

        $this->assertCount(1, $this->packages(), 'the same vendor/name twice must reuse one Package');
        $this->assertCount(1, $this->packageVersions());
    }

    public function testSamePackageWithTwoVersionsCreatesTwoVersions(): void
    {
        $this->factory()->setPackageVersions(new Installation(), [
            self::installed('acme/foo', '1.0.0'),
            self::installed('acme/foo', '2.0.0'),
        ]);

        $this->assertCount(1, $this->packages());
        $this->assertCount(2, $this->packageVersions(), 'distinct versions of one package are distinct rows');
    }

    /**
     * Two brand-new packages sharing a version string must not collapse into one
     * PackageVersion. Keying the buffer on object identity is what keeps them
     * apart — neither package has an id yet, because nothing has been flushed.
     */
    public function testTwoNewPackagesSharingAVersionStringStayApart(): void
    {
        $this->factory()->setPackageVersions(new Installation(), [
            self::installed('acme/foo', '1.0.0'),
            self::installed('acme/bar', '1.0.0'),
        ]);

        $this->assertCount(2, $this->packages());
        $this->assertCount(2, $this->packageVersions(), 'a shared version string must not merge two packages');
    }

    /**
     * The point of making the service stateless: a second call cannot reuse the
     * first call's entities, even on the same instance.
     */
    public function testNothingCarriesOverBetweenCalls(): void
    {
        $factory = $this->factory();

        $factory->setPackageVersions(new Installation(), [self::installed('acme/foo', '1.0.0')]);
        $factory->setPackageVersions(new Installation(), [self::installed('acme/foo', '1.0.0')]);

        $packages = array_values($this->packages());
        $this->assertCount(2, $packages, 'each call starts from an empty buffer');
        $this->assertNotSame($packages[0], $packages[1]);
    }

    /**
     * The buffers used to be cleared after flush() rather than in a finally, so
     * a failing flush left them populated for the next call — holding entities
     * attached to an EntityManager that had since closed. Locals cannot do that.
     */
    public function testAFailedFlushLeavesNothingForTheNextCall(): void
    {
        $factory = $this->factory(throwOnFirstFlush: true);

        try {
            $factory->setPackageVersions(new Installation(), [self::installed('acme/foo', '1.0.0')]);
            $this->fail('expected the failing flush to throw');
        } catch (\RuntimeException) {
            // Expected. What matters is the state left behind.
        }

        $this->persisted = [];
        $factory->setPackageVersions(new Installation(), [self::installed('acme/foo', '1.0.0')]);

        $this->assertCount(1, $this->packages(), 'the package is created afresh, not taken from a stale buffer');
    }

    private function factory(bool $throwOnFirstFlush = false): PackageVersionFactory
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

        // Nothing is in the database, so every lookup misses and the factory is
        // forced onto its in-call buffers — which is what we want to exercise.
        $packageRepository = $this->createStub(PackageRepository::class);
        $packageRepository->method('findOneBy')->willReturn(null);
        $packageVersionRepository = $this->createStub(PackageVersionRepository::class);
        $packageVersionRepository->method('findOneBy')->willReturn(null);

        return new PackageVersionFactory($entityManager, $packageRepository, $packageVersionRepository);
    }

    private static function installed(string $name, string $version): object
    {
        return (object) [
            'name' => $name,
            'version' => $version,
            'description' => 'A package.',
        ];
    }

    /** @return array<int, Package> */
    private function packages(): array
    {
        return array_filter($this->persisted, static fn (object $e): bool => $e instanceof Package);
    }

    /** @return array<int, PackageVersion> */
    private function packageVersions(): array
    {
        return array_filter($this->persisted, static fn (object $e): bool => $e instanceof PackageVersion);
    }
}
