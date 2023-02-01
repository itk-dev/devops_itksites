<?php

namespace App\Controller\Admin;

use App\Admin\Field\VersionField;
use App\Entity\GitTag;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class GitTagCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return GitTag::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setDefaultSort(['repo.organization' => 'ASC', 'repo.repo' => 'ASC'])
        ;
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
        yield AssociationField::new('repo')->setColumns(6);
        yield AssociationField::new('installations')->setColumns(6);
        yield VersionField::new('tag')->setColumns(6);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('repo')
            ->add('tag')
        ;
    }
}
