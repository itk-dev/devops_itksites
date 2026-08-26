<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\AdvisoryRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Theme;
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
            ->renderContentMaximized()
            // ITK blue. Since EasyAdmin 5.4 one primary colour drives buttons,
            // links, the active sidebar item and boolean badges, and the theme
            // computes the text colour that sits on top of it — which the
            // stylesheet used to approximate variable by variable.
            ->setTheme(Theme::new()->primaryColor('#007ba6'));
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
        // `?: null` because EasyAdmin hides a badge whose content is null but
        // renders a literal "0" for a zero count, which is noise on a menu item.
        yield MenuItem::linkTo(AdvisoryCrudController::class, 'Advisories', 'fas fa-skull-crossbones')->setBadge($this->advisoryRepository->count([]) ?: null, 'dark');
        yield MenuItem::linkTo(ModuleCrudController::class, 'Modules', 'fas fa-cube');
        yield MenuItem::linkTo(ModuleVersionCrudController::class, 'Modules Versions', 'fas fa-cubes');
        yield MenuItem::linkTo(DockerImageCrudController::class, 'Docker Images', 'fas fa-cube');
        yield MenuItem::linkTo(DockerImageTagCrudController::class, 'Docker Image Tags', 'fas fa-cubes');
        yield MenuItem::linkTo(GitRepoCrudController::class, 'Git Repositories', 'fa-brands fa-github');
        yield MenuItem::linkTo(GitTagCrudController::class, 'Git Tags', 'fa-brands fa-git-alt');
        yield MenuItem::section('Results');
        yield MenuItem::linkTo(DetectionResultCrudController::class, 'Detection Results', 'fas fa-upload');
    }

    /**
     * The admin styles reach admin pages only from here.
     *
     * EasyAdmin renders its own layout rather than templates/base.html.twig, so
     * neither `importmap()` nor that template's Encore tags apply to it. Until
     * now this method added `css/admin.css`, a file deleted in #81, so every
     * admin page carried a 404 and none of the ITK styling below it.
     */
    #[\Override]
    public function configureAssets(): Assets
    {
        return Assets::new()->addWebpackEncoreEntry('admin');
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
}
