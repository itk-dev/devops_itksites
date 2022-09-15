<?php

namespace App\Controller\Admin;

use App\Entity\DetectionResult;
use App\Entity\DockerImage;
use App\Entity\DockerImageTag;
use App\Entity\Domain;
use App\Entity\Installation;
use App\Entity\Module;
use App\Entity\ModuleVersion;
use App\Entity\Package;
use App\Entity\PackageVersion;
use App\Entity\Server;
use App\Entity\Site;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator
    ) {
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $d = $this->adminUrlGenerator
            ->setController(ServerCrudController::class)->setAction(Crud::PAGE_INDEX)
            ->generateUrl();

        return $this->redirect($d);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('ITK Sites')
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Servers', 'fas fa-server', Server::class);
        yield MenuItem::linkToCrud('Installations', 'fas fa-folder', Installation::class);
        yield MenuItem::linkToCrud('Sites', 'fas fa-bookmark', Site::class);
        yield MenuItem::linkToCrud('Domains', 'fas fa-link', Domain::class);
        yield MenuItem::section('Dependencies');
        yield MenuItem::linkToCrud('Packages', 'fas fa-cube', Package::class);
        yield MenuItem::linkToCrud('Package Versions', 'fas fa-cubes', PackageVersion::class);
        yield MenuItem::linkToCrud('Modules', 'fas fa-cube', Module::class);
        yield MenuItem::linkToCrud('Modules Versions', 'fas fa-cubes', ModuleVersion::class);
        yield MenuItem::linkToCrud('Docker Images', 'fas fa-cube', DockerImage::class);
        yield MenuItem::linkToCrud('Docker Image Tags', 'fas fa-cubes', DockerImageTag::class);
        yield MenuItem::section('Results');
        yield MenuItem::linkToCrud('Detection Results', 'fas fa-upload', DetectionResult::class);
    }

    public function configureCrud(): Crud
    {
        return Crud::new()
            // this defines the pagination size for all CRUD controllers
            // (each CRUD controller can override this value if needed)
            ->setDateTimeFormat('yyyy-MM-dd HH:mm:ss');
    }
}
