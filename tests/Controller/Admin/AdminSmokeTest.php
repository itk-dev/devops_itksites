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
     * The deprecated CRUD controllers are gone from the admin menu but still
     * routed, so the only way in is a bookmark or an old link. Each page says
     * so; without the warning it looks like a maintained part of the admin.
     */
    #[DataProvider('deprecatedCrudControllerProvider')]
    public function testDeprecatedCrudIndexPageWarns(string $controllerClass): void
    {
        $client = static::createClient();

        $user = static::getContainer()->get('doctrine')->getManager()
            ->getRepository(User::class)->findOneBy([]);
        $client->loginUser($user);

        $url = static::getContainer()->get(AdminUrlGenerator::class)
            ->setController($controllerClass)
            ->setAction(Crud::PAGE_INDEX)
            ->generateUrl();

        $crawler = $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('#flash-messages .alert-title', 'Deprecated');
        $this->assertStringContainsString(
            'deprecated and no longer maintained here',
            $crawler->filter('#flash-messages')->text()
        );
    }

    public function testMaintainedCrudIndexPageDoesNotWarn(): void
    {
        $client = static::createClient();

        $user = static::getContainer()->get('doctrine')->getManager()
            ->getRepository(User::class)->findOneBy([]);
        $client->loginUser($user);

        $url = static::getContainer()->get(AdminUrlGenerator::class)
            ->setController(ServerCrudController::class)
            ->setAction(Crud::PAGE_INDEX)
            ->generateUrl();

        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('#flash-messages');
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function deprecatedCrudControllerProvider(): iterable
    {
        yield 'OIDC' => [OIDCCrudController::class];
        yield 'ServiceCertificate' => [ServiceCertificateCrudController::class];
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
