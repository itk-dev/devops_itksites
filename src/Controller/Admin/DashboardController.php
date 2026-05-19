<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\AdvisoryRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly AdvisoryRepository $advisoryRepository,
    ) {
    }

    #[\Override]
    public function index(): Response
    {
        $d = $this->adminUrlGenerator
            ->setController(ServerCrudController::class)->setAction(Crud::PAGE_INDEX)
            ->generateUrl();

        return $this->redirect($d);
    }

    #[\Override]
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<img src="/img/itk-sites-logo.png" width="170px" alt="ITK sites logo">')
            ->setFaviconPath('img/favicon.ico')
            ->renderContentMaximized();
    }

    #[\Override]
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkTo(ServerCrudController::class, 'Servers', 'fas fa-server');
        yield MenuItem::linkTo(InstallationCrudController::class, 'Installations', 'fas fa-folder');
        yield MenuItem::linkTo(SiteCrudController::class, 'Sites', 'fas fa-bookmark');
        yield MenuItem::linkTo(DomainCrudController::class, 'Domains', 'fas fa-link');
        yield MenuItem::linkTo(OIDCCrudController::class, 'OIDC', 'fas fa-key');
        yield MenuItem::linkTo(ServiceCertificateCrudController::class, 'Service certificates', 'fas fa-lock');
        yield MenuItem::linkTo(SecurityContractCrudController::class, 'Service Agreements', 'fas fa-file-contract');
        yield MenuItem::section('Dependencies');
        yield MenuItem::linkTo(PackageCrudController::class, 'Packages', 'fas fa-cube');
        yield MenuItem::linkTo(PackageVersionCrudController::class, 'Package Versions', 'fas fa-cubes');
        yield MenuItem::linkTo(AdvisoryCrudController::class, 'Advisories', 'fas fa-skull-crossbones')->setBadge($this->advisoryRepository->count([]), 'dark');
        yield MenuItem::linkTo(ModuleCrudController::class, 'Modules', 'fas fa-cube');
        yield MenuItem::linkTo(ModuleVersionCrudController::class, 'Modules Versions', 'fas fa-cubes');
        yield MenuItem::linkTo(DockerImageCrudController::class, 'Docker Images', 'fas fa-cube');
        yield MenuItem::linkTo(DockerImageTagCrudController::class, 'Docker Image Tags', 'fas fa-cubes');
        yield MenuItem::linkTo(GitRepoCrudController::class, 'Git Repositories', 'fa-brands fa-github');
        yield MenuItem::linkTo(GitTagCrudController::class, 'Git Tags', 'fa-brands fa-git-alt');
        yield MenuItem::section('Results');
        yield MenuItem::linkTo(DetectionResultCrudController::class, 'Detection Results', 'fas fa-upload');
    }

    #[\Override]
    public function configureCrud(): Crud
    {
        return Crud::new()
            // this defines the pagination size for all CRUD controllers
            // (each CRUD controller can override this value if needed)
            ->setDateTimeFormat('yyyy-MM-dd HH:mm:ss')
            ->setPageTitle('detail', '%entity_label_singular%: %entity_as_string%')
        ;
    }

    #[\Override]
    public function configureAssets(): Assets
    {
        return parent::configureAssets()->addAssetMapperEntry('app');
    }
}
