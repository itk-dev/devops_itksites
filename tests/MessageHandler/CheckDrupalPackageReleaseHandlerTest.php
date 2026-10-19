<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Entity\Package;
use App\Message\CheckDrupalPackageRelease;
use App\MessageHandler\CheckDrupalPackageReleaseHandler;
use App\Repository\PackageRepository;
use App\Service\Drupal\ReleaseChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

class CheckDrupalPackageReleaseHandlerTest extends TestCase
{
    public function testChecksThePackage(): void
    {
        $id = new Ulid();
        $package = new Package();
        $repository = $this->createMock(PackageRepository::class);
        $repository->expects($this->once())->method('find')->with($id)->willReturn($package);
        $checker = $this->createMock(ReleaseChecker::class);
        $checker->expects($this->once())->method('check')->with($package);

        new CheckDrupalPackageReleaseHandler($repository, $checker)(new CheckDrupalPackageRelease($id));
    }

    public function testSkipsAMissingPackage(): void
    {
        $repository = $this->createStub(PackageRepository::class);
        $repository->method('find')->willReturn(null);
        $checker = $this->createMock(ReleaseChecker::class);
        $checker->expects($this->never())->method('check');

        new CheckDrupalPackageReleaseHandler($repository, $checker)(new CheckDrupalPackageRelease(new Ulid()));
    }
}
