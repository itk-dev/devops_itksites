<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\EasyAdmin\Config\AutoBadgeMenuItem;
use App\Entity\Advisory;
use App\Entity\DetectionResult;
use App\Entity\DockerImage;
use App\Entity\DockerImageTag;
use App\Entity\Domain;
use App\Entity\GitRepo;
use App\Entity\GitTag;
use App\Entity\Installation;
use App\Entity\Module;
use App\Entity\ModuleVersion;
use App\Entity\OIDC;
use App\Entity\Package;
use App\Entity\PackageVersion;
use App\Entity\Project;
use App\Entity\SecurityContract;
use App\Entity\Server;
use App\Entity\ServiceCertificate;
use App\Entity\Site;
use App\Repository\AdvisoryRepository;
use App\Repository\OIDCRepository;
use App\Repository\SecurityContractRepository;
use App\Repository\ServiceCertificateRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly AdvisoryRepository $advisoryRepository,
        private readonly ServiceCertificateRepository $serviceCertificateRepository,
        private readonly OIDCRepository $oidcRepository,
        private readonly SecurityContractRepository $securityContractRepository,
    ) {
    }

    #[\Symfony\Component\Routing\Attribute\Route('/admin', name: 'admin')]
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

        yield MenuItem::section('Projects');
        yield MenuItem::linkToCrud('Projects', 'fas fa-folder', Project::class);
        yield AutoBadgeMenuItem::linkToCrud('Cyber Security', 'fas fa-file-contract', SecurityContract::class)
            ->setBadge($this->securityContractRepository->countExpiredContracts(), 'danger');
        yield AutoBadgeMenuItem::linkToCrud('OIDC', 'fas fa-shield-halved', OIDC::class)
            ->setBadge($this->oidcRepository->countExpiredCertificates(), 'danger');
        yield AutoBadgeMenuItem::linkToCrud('Service certificates', 'fas fa-passport', ServiceCertificate::class)
            ->setBadge($this->serviceCertificateRepository->countExpiredCertificates(), 'danger');

        yield MenuItem::section('Hosting');
        yield MenuItem::linkToCrud('Servers', 'fas fa-server', Server::class);
        yield MenuItem::linkToCrud('Installations', 'fas fa-folder', Installation::class);
        yield MenuItem::linkToCrud('Sites', 'fas fa-bookmark', Site::class);
        yield MenuItem::linkToCrud('Domains', 'fas fa-link', Domain::class);

        yield MenuItem::section('Dependencies');
        yield MenuItem::linkToCrud('Packages', 'fas fa-cube', Package::class);
        yield MenuItem::linkToCrud('Package Versions', 'fas fa-cubes', PackageVersion::class);
        yield AutoBadgeMenuItem::linkToCrud('Advisories', 'fas fa-skull-crossbones', Advisory::class)
            ->setBadge($this->advisoryRepository->count([]), 'danger');
        yield MenuItem::linkToCrud('Modules', 'fas fa-cube', Module::class);
        yield MenuItem::linkToCrud('Modules Versions', 'fas fa-cubes', ModuleVersion::class);
        yield MenuItem::linkToCrud('Docker Images', 'fas fa-cube', DockerImage::class);
        yield MenuItem::linkToCrud('Docker Image Tags', 'fas fa-cubes', DockerImageTag::class);
        yield MenuItem::linkToCrud('Git Repositories', 'fa-brands fa-github', GitRepo::class);
        yield MenuItem::linkToCrud('Git Tags', 'fa-brands fa-git-alt', GitTag::class);
        yield MenuItem::section('Results');
        yield MenuItem::linkToCrud('Detection Results', 'fas fa-upload', DetectionResult::class);
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
