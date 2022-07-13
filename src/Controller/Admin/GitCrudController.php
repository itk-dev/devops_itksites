<?php

namespace App\Controller\Admin;

use App\Entity\Git;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class GitCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Git::class;
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
        yield AssociationField::new('server')->setColumns(6);
        yield TextField::new('rootDir')->setColumns(12);
        yield AssociationField::new('remotes')->setColumns(1)->setCrudController(GitRemoteCrudController::class)->autocomplete();
        yield TextField::new('tag')->setColumns(2);
        yield FormField::addRow();
        yield IntegerField::new('changesCount')->setColumns(1);
        yield TextField::new('changes')->setColumns(1)->hideOnIndex();
        yield DateTimeField::new('createdAt');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('server')
            ->add('rootDir')
            ->add('tag')
            ->add('changesCount')
            ;
    }
}
