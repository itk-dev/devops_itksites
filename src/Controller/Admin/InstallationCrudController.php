<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Admin\Field\ChangesField;
use App\Admin\Field\EolTypeField;
use App\Admin\Field\RootDirField;
use App\Admin\Field\ServerTypeField;
use App\Admin\Field\VersionField;
use App\Entity\Installation;
use App\Form\Type\Admin\FrameworkFilter;
use App\Form\Type\Admin\SystemFilter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

class InstallationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Installation::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud->showEntityActionsInlined();
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE);
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield VersionField::new('type');
        yield VersionField::new('frameworkVersion', 'ver.');
        yield BooleanField::new('lts')->renderAsSwitch(false)->hideOnIndex();
        yield EolTypeField::new('eol')->hideOnIndex();
        yield VersionField::new('composerVersion', 'Comp.');
        yield ChangesField::new('gitChangesCount')->hideOnDetail()->setLabel('Git');
        yield VersionField::new('gitTag.tag')->hideOnDetail()->setLabel('Tag');
        yield AssociationField::new('gitTag')->hideOnIndex();
        yield ChangesField::new('gitChangesCount')->hideOnIndex();
        yield CodeEditorField::new('gitChanges')->hideOnIndex();
        yield AssociationField::new('sites')->hideOnIndex();
        yield RootDirField::new('rootDir')->setColumns(12);
        yield ServerTypeField::new('server.type')->setLabel('Type');
        yield AssociationField::new('server');
        yield AssociationField::new('detectionResult')->hideOnIndex();
        yield DateTimeField::new('createdAt')->hideOnIndex();
        yield DateTimeField::new('detectionResult.lastContact')->hideOnIndex();
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(FrameworkFilter::new('type'))
            ->add('frameworkVersion')
            ->add('lts')
            ->add('eol')
            ->add('composerVersion')
            ->add('rootDir')
            ->add('server')
//            ->add(SystemFilter::new('system')->mapped(false))
        ;
    }
}
