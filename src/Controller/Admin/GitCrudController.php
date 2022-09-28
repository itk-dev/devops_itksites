<?php

namespace App\Controller\Admin;

use App\Admin\Field\RootDirField;
use App\Admin\Field\ServerTypeField;
use App\Entity\Git;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
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
        return $crud
            ->showEntityActionsInlined()
            ->setDefaultSort(['server.name' => 'ASC', 'rootDir' => 'ASC'])
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
        yield RootDirField::new('rootDir');
        yield AssociationField::new('remotes')->hideOnIndex();
        yield TextField::new('tag');
        yield IntegerField::new('changesCount', 'Changes')->hideOnDetail();
        yield CodeEditorField::new('changes')->hideOnIndex();
        yield ServerTypeField::new('server.type')->setLabel('Type');
        yield AssociationField::new('server');
        yield AssociationField::new('detectionResult')->hideOnIndex();
        yield DateTimeField::new('createdAt')->hideOnIndex();
        yield DateTimeField::new('detectionResult.lastContact')->hideOnIndex();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('server')
            ->add('rootDir')
            ->add('remotes')
            ->add('tag')
            ->add('changesCount')
        ;
    }
}
