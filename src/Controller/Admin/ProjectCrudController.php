<?php

namespace App\Controller\Admin;

use App\Admin\Field\VersionField;
use App\Entity\Project;
use App\Repository\SiteRepository;
use App\Service\Leantime\ProjectSyncService;
use App\Trait\ExportCrudControllerTrait;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Translation\TranslatableMessage;

class ProjectCrudController extends AbstractCrudController
{
    use ExportCrudControllerTrait;

    public function __construct(
        private readonly ProjectSyncService $projectSyncService,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Project::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['name' => 'ASC'])
            ->setSearchFields(['name', 'details'])
            ->showEntityActionsInlined()
            ->setPageTitle(Crud::PAGE_INDEX, 'Projects')
            ->setHelp(Crud::PAGE_INDEX, 'Projects are synced from Leantime. Click on the "Sync all" button to update all projects.');
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::DELETE, Action::NEW, Action::EDIT)
            ->add(Crud::PAGE_INDEX, $this->createLeantimeAction())
            ->add(Crud::PAGE_DETAIL, $this->createLeantimeAction())
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $this->createExportAction())
            ->add(Crud::PAGE_INDEX, $this->createUpdateAllProjectsAction());
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield VersionField::new('LeantimeId')->setLabel('id');
        yield TextField::new('name');
        yield TextEditorField::new('details')->formatValue(fn ($value) => strip_tags($value));
        yield DateTimeField::new('createdAt')->hideOnIndex();
    }

    public function createLeantimeAction(): Action
    {
        return Action::new('leantime', $label ?? new TranslatableMessage('Leantime'), 'fa fa-link')
            ->linkToUrl(function (Project $project) {
                return $project->getLeantimeUrl();
            });
    }

    public function createUpdateAllProjectsAction(): Action
    {
        return Action::new('update', $label ?? new TranslatableMessage('Sync all'), 'fa fa-rotate')
            ->createAsGlobalAction()
            ->linkToCrudAction('updateAllProjects');
    }

    public function updateAllProjects(SiteRepository $siteRepository): RedirectResponse
    {
        try {
            $this->projectSyncService->syncAllProjects();

            $this->addFlash('info', 'All projects have been synced.', );
        } catch (\Throwable $e) {
            $this->addFlash('error', 'An error occurred while syncing projects. Check the log for details.');
        }

        return $this->redirectToRoute('admin_project_index');
    }
}
