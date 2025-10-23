<?php

namespace App\Controller\Admin;

use App\Trait\ExportCrudControllerTrait;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\ActionGroup;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

abstract class AbstractFullCrudController extends AbstractCrudController
{
    use ExportCrudControllerTrait;

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud->showEntityActionsInlined();
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        // Remove default actions
        $actions
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE);

        // Re-add default actions as grouped action.
        $groupedDefaultActions = ActionGroup::new('default', 'Default')
            ->addMainAction(
                Action::new('show', 'Show')
                    ->linkToCrudAction(Action::DETAIL)
            )
            ->addAction(
                Action::new('edit', 'Edit')
                    ->linkToCrudAction(Action::EDIT)
                    ->setIcon('fa fa-edit')
            )
            ->addDivider()
            ->addAction(
                Action::new('delete', 'Delete')
                    ->linkToCrudAction(Action::DELETE)
                    ->setIcon('fa fa-trash')
                    ->setCssClass('btn-danger text-danger')
            );

        return $actions
            ->add(Crud::PAGE_INDEX, $groupedDefaultActions)
            ->add(Crud::PAGE_INDEX, $this->createExportAction())
            ->update(Crud::PAGE_INDEX, Action::NEW,
                static fn (Action $action) => $action->setIcon('fa fa-plus')
            )
            ;
    }

    #[\Override]
    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addWebpackEncoreEntry('easyadmin');
    }
}