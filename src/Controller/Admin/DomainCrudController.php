<?php

namespace App\Controller\Admin;

use App\Admin\Field\DomainField;
use App\Admin\Field\ServerTypeField;
use App\Admin\Field\SiteTypeField;
use App\Entity\Domain;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

class DomainCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Domain::class;
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
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield DomainField::new('address')->setColumns(12);
        yield SiteTypeField::new('site.type')->hideOnIndex();
        yield AssociationField::new('site')->hideOnIndex();
        yield ServerTypeField::new('server.type')->setLabel('Type');
        yield AssociationField::new('server');
        yield AssociationField::new('detectionResult')->hideOnIndex();
        yield DateTimeField::new('createdAt')->hideOnIndex();
        yield DateTimeField::new('detectionResult.lastContact')->hideOnIndex();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('address')
            ->add('site')
            ->add('server')
        ;
    }
}
