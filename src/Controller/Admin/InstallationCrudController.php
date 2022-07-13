<?php

namespace App\Controller\Admin;

use App\Admin\Field\EolTypeField;
use App\Admin\Field\RootDirField;
use App\Admin\Field\ServerTypeField;
use App\Admin\Field\VersionField;
use App\Entity\Installation;
use App\Form\Type\Admin\FrameworkFilter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

class InstallationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Installation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->showEntityActionsInlined();
    }

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

    public function configureFields(string $pageName): iterable
    {
        yield VersionField::new('type');
        yield VersionField::new('frameworkVersion', 'ver.');
        yield BooleanField::new('lts')->renderAsSwitch(false);
        yield EolTypeField::new('eol');
        yield VersionField::new('composerVersion', 'Comp.');
        yield AssociationField::new('sites');
        yield RootDirField::new('rootDir')->setColumns(12);
        yield ServerTypeField::new('server.type')->setLabel('Type');
        yield AssociationField::new('server');
        yield AssociationField::new('detectionResult')->hideOnIndex();
        yield DateTimeField::new('createdAt');
        yield DateTimeField::new('detectionResult.lastContact')->hideOnIndex();
    }

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
            ;
    }
}
