<?php

namespace App\Controller\Admin;

use App\Entity\DetectionResult;
use App\Entity\Domain;
use App\Entity\Installation;
use App\Entity\Server;
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
        private AdminUrlGenerator $adminUrlGenerator
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
        yield MenuItem::linkToCrud('Installations', 'fas fa-folder', Installation::class);
        yield MenuItem::linkToCrud('Domains', 'fas fa-link', Domain::class);
        yield MenuItem::linkToCrud('Servers', 'fas fa-server', Server::class);
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
