<?php

namespace App\Controller\Admin;

use App\Admin\Field\AdvisoryCountField;
use App\Admin\Field\TextMonospaceField;
use App\Admin\Field\WarningField;
use App\Entity\Package;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class PackageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Package::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setDefaultSort(['advisoryCount' => 'DESC', 'warning' => 'DESC', 'vendor' => 'ASC', 'name' => 'ASC']);
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
        yield TextMonospaceField::new('vendor')->setColumns(6);
        yield TextMonospaceField::new('name')->setColumns(6);
        yield UrlField::new('packagistUrl')->setColumns(6)->hideOnIndex();
        yield AssociationField::new('packageVersions')->setColumns(6)->setLabel('Versions');
        yield AdvisoryCountField::new('advisoryCount')->onlyOnIndex()->setLabel('Adv.')->setCssClass('text-center');
        yield WarningField::new('warning')->onlyOnIndex()->setLabel('War.')->setCssClass('text-center');
        yield TextField::new('description')->setColumns(12)->hideOnIndex();
        yield WarningField::new('warning')->onlyOnDetail();
        yield AssociationField::new('advisories')->onlyOnDetail();
        yield TextField::new('description')->setColumns(12)->hideOnDetail();
        yield DateTimeField::new('createdAt')->hideOnIndex();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('vendor')
            ->add('name')
            ->add('type')
            ->add('license')
        ;
    }
}
