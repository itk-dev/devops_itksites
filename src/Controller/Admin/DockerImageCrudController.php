<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Admin\Field\TextMonospaceField;
use App\Entity\DockerImage;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DockerImageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DockerImage::class;
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
        yield TextMonospaceField::new('organization')->setColumns(6);
        yield TextMonospaceField::new('repository')->setColumns(6);
        yield AssociationField::new('dockerImageTags')->setColumns(6);
        yield TextField::new('description')->setColumns(12)->hideOnIndex();
        yield DateTimeField::new('createdAt')->hideOnIndex();
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('organization')
            ->add('repository')
        ;
    }
}
