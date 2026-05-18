<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\AdvisoryCrudController;
use App\Controller\Admin\DetectionResultCrudController;
use App\Controller\Admin\DockerImageCrudController;
use App\Controller\Admin\DockerImageTagCrudController;
use App\Controller\Admin\DomainCrudController;
use App\Controller\Admin\GitRepoCrudController;
use App\Controller\Admin\GitTagCrudController;
use App\Controller\Admin\InstallationCrudController;
use App\Controller\Admin\ModuleCrudController;
use App\Controller\Admin\ModuleVersionCrudController;
use App\Controller\Admin\OIDCCrudController;
use App\Controller\Admin\PackageCrudController;
use App\Controller\Admin\PackageVersionCrudController;
use App\Controller\Admin\ServerCrudController;
use App\Controller\Admin\ServiceCertificateCrudController;
use App\Controller\Admin\SiteCrudController;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminSmokeTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    #[DataProvider('crudControllerProvider')]
    public function testCrudIndexPageLoads(string $controllerClass): void
    {
        $client = static::createClient();

        $user = static::getContainer()->get('doctrine')->getManager()
            ->getRepository(User::class)->findOneBy([]);
        $client->loginUser($user);

        $url = static::getContainer()->get(AdminUrlGenerator::class)
            ->setController($controllerClass)
            ->setAction(Crud::PAGE_INDEX)
            ->generateUrl();

        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function crudControllerProvider(): iterable
    {
        yield 'Advisory' => [AdvisoryCrudController::class];
        yield 'DetectionResult' => [DetectionResultCrudController::class];
        yield 'DockerImage' => [DockerImageCrudController::class];
        yield 'DockerImageTag' => [DockerImageTagCrudController::class];
        yield 'Domain' => [DomainCrudController::class];
        yield 'GitRepo' => [GitRepoCrudController::class];
        yield 'GitTag' => [GitTagCrudController::class];
        yield 'Installation' => [InstallationCrudController::class];
        yield 'Module' => [ModuleCrudController::class];
        yield 'ModuleVersion' => [ModuleVersionCrudController::class];
        yield 'OIDC' => [OIDCCrudController::class];
        yield 'Package' => [PackageCrudController::class];
        yield 'PackageVersion' => [PackageVersionCrudController::class];
        yield 'Server' => [ServerCrudController::class];
        yield 'ServiceCertificate' => [ServiceCertificateCrudController::class];
        yield 'Site' => [SiteCrudController::class];
    }
}
