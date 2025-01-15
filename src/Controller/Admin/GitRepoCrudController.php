<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Admin\Field\TextMonospaceField;
use App\Entity\GitRepo;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class GitRepoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return GitRepo::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setDefaultSort(['organization' => 'ASC', 'repo' => 'ASC'])
        ;
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
        yield TextField::new('provider')->setColumns(6);
        yield UrlField::new('url')->setColumns(6)->hideOnIndex();
        yield TextMonospaceField::new('organization')->setColumns(6);
        yield TextMonospaceField::new('repo')->setColumns(6);
        yield AssociationField::new('gitTags');
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('provider')
            ->add('organization')
            ->add('repo')
        ;
    }
}
