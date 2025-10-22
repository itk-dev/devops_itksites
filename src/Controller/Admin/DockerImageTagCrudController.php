<?php

declare(strict_types=1);

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

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud->showEntityActionsInlined();
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::DELETE, Action::NEW, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('dockerImage')->setColumns(6);
        yield AssociationField::new('installations')->setColumns(6);
        yield VersionField::new('tag')->setColumns(6);
        yield DateTimeField::new('createdAt')->hideOnIndex();
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('dockerImage')
            ->add('tag')
        ;
    }
}
