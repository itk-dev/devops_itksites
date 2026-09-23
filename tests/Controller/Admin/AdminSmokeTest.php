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
use App\Entity\Advisory;
use App\Entity\Installation;
use App\Entity\Module;
use App\Entity\ModuleVersion;
use App\Entity\Package;
use App\Entity\PackageVersion;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
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
     * A module reads its advisories through the linked Composer package, so
     * the count, its sort and the detail table have to render through it.
     */
    #[DataProvider('moduleCrudControllerProvider')]
    public function testModuleAdvisoriesRender(string $controllerClass, string $sortProperty, int $entityIndex): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $client->loginUser($entityManager->getRepository(User::class)->findOneBy([]));
        $entities = $this->createModuleWithAdvisory($entityManager);
        $linkedId = (string) $entities[$entityIndex]->getId();
        // A reboot between requests drops the rows created above.
        $client->disableReboot();

        foreach (['DESC' => true, 'ASC' => false] as $direction => $linkedFirst) {
            $url = static::getContainer()->get(AdminUrlGenerator::class)
                ->setController($controllerClass)
                ->setAction(Crud::PAGE_INDEX)
                ->set('sort', [$sortProperty => $direction])
                ->generateUrl();

            $crawler = $client->request('GET', $url);

            $this->assertResponseIsSuccessful();
            $firstId = $crawler->filter('tbody tr[data-id]')->first()->attr('data-id');
            $this->assertSame($linkedFirst, $linkedId === $firstId, $direction);
        }
        $this->assertSelectorTextContains('tr[data-id="'.$linkedId.'"] .badge-danger', '1');

        $url = static::getContainer()->get(AdminUrlGenerator::class)
            ->setController($controllerClass)
            ->setAction(Crud::PAGE_DETAIL)
            ->setEntityId($linkedId)
            ->generateUrl();

        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $this->assertAnySelectorTextContains('td', 'Module smoke advisory');
        $this->assertAnySelectorTextContains('a', 'drupal/smoke_probe');
    }

    /**
     * @return iterable<string, array{string, string, int}>
     */
    public static function moduleCrudControllerProvider(): iterable
    {
        yield 'Module' => [ModuleCrudController::class, 'composerPackage.advisoryCount', 0];
        yield 'ModuleVersion' => [ModuleVersionCrudController::class, 'composerPackageVersion.advisoryCount', 1];
    }

    #[DataProvider('packageCrudControllerProvider')]
    public function testPackageDetailShowsLinkedModules(string $controllerClass, int $entityIndex, int $linkedIndex): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $client->loginUser($entityManager->getRepository(User::class)->findOneBy([]));
        $entities = $this->createModuleWithAdvisory($entityManager);

        $url = static::getContainer()->get(AdminUrlGenerator::class)
            ->setController($controllerClass)
            ->setAction(Crud::PAGE_DETAIL)
            ->setEntityId($entities[$entityIndex]->getId())
            ->generateUrl();

        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('a[href*="'.$entities[$linkedIndex]->getId().'"]');
    }

    /**
     * @return iterable<string, array{string, int, int}>
     */
    public static function packageCrudControllerProvider(): iterable
    {
        yield 'Package' => [PackageCrudController::class, 2, 0];
        yield 'PackageVersion' => [PackageVersionCrudController::class, 3, 1];
    }

    /**
     * A linked module and module version with one advisory, next to an
     * unlinked module and module version to sort against.
     *
     * @return array{Module, ModuleVersion, Package, PackageVersion}
     */
    private function createModuleWithAdvisory(ObjectManager $entityManager): array
    {
        $package = new Package()->setVendor('drupal')->setName('smoke_probe');
        $packageVersion = new PackageVersion()->setVersion('1.0.0');
        $package->addPackageVersion($packageVersion);
        $advisory = new Advisory()
            ->setAdvisoryId('SA-CONTRIB-2026-999')
            ->setAffectedVersions('<1.0.1')
            ->setTitle('Module smoke advisory')
            ->setLink('https://www.drupal.org/sa-contrib-2026-999')
            ->setReportedAt(new \DateTimeImmutable());
        $package->addAdvisory($advisory);
        $packageVersion->addAdvisory($advisory);

        $module = new Module()->setName('smoke_probe')->setPackage('Other')->setEnabled(true);
        $moduleVersion = new ModuleVersion()->setVersion('1.0.0');
        $module->addModuleVersion($moduleVersion);
        $package->addModule($module);
        $packageVersion->addModuleVersion($moduleVersion);

        $unlinkedModule = new Module()->setName('smoke_unlinked')->setPackage('Other')->setEnabled(true);
        $unlinkedModuleVersion = new ModuleVersion()->setVersion('1.0.0');
        $unlinkedModule->addModuleVersion($unlinkedModuleVersion);

        foreach ([$package, $packageVersion, $advisory, $module, $moduleVersion, $unlinkedModule, $unlinkedModuleVersion] as $entity) {
            $entityManager->persist($entity);
        }
        // Rows no installation uses are deleted on flush.
        $entityManager->getRepository(Installation::class)->findOneBy([])
            ->addPackageVersion($packageVersion)
            ->addModuleVersion($moduleVersion)
            ->addModuleVersion($unlinkedModuleVersion);
        $entityManager->flush();

        return [$module, $moduleVersion, $package, $packageVersion];
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
