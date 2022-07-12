<?php

namespace App\Controller\Admin;

use App\Admin\Field\VersionField;
use App\Entity\DockerImageTag;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

class DockerImageTagCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DockerImageTag::class;
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
        yield AssociationField::new('dockerImage')->setColumns(6);
        yield AssociationField::new('installations')->setColumns(6);
        yield VersionField::new('tag')->setColumns(6);
        yield DateTimeField::new('createdAt');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('dockerImage')
            ->add('tag')
            ;
    }
}
