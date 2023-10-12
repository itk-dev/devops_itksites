<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Admin\Field\SourcesField;
use App\Admin\Field\TextMonospaceField;
use App\Entity\Advisory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class AdvisoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Advisory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setDefaultSort(['package.vendor' => 'ASC', 'package.name' => 'ASC']);
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
        yield TextMonospaceField::new('advisoryId')->setColumns(6)->onlyOnDetail();
        yield AssociationField::new('package');
        yield TextMonospaceField::new('affectedVersions')->setColumns(6)->onlyOnDetail();
        yield AssociationField::new('packageVersions')->setLabel('Versions');
        yield TextMonospaceField::new('cve')->setColumns(6)->setLabel('CVE');
        yield TextField::new('title')->setColumns(6);
        yield UrlField::new('link')->setColumns(6)->onlyOnDetail();
        yield DateField::new('reportedAt')->setColumns(6)->onlyOnIndex();
        yield DateTimeField::new('reportedAt')->setColumns(6)->onlyOnDetail();
        yield SourcesField::new('sourceLinks')->setColumns(6)->onlyOnDetail();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('package')
            ->add('advisoryId')
            ->add('cve')
            ->add('reportedAt')
        ;
    }
}
