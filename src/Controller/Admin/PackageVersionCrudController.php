<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Admin\Field\AdvisoryCountField;
use App\Admin\Field\LatestStatusField;
use App\Admin\Field\VersionField;
use App\Entity\PackageVersion;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class PackageVersionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PackageVersion::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setDefaultSort(['advisoryCount' => 'DESC', 'package' => 'ASC']);
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
        yield AssociationField::new('package')->setColumns(6);
        yield VersionField::new('version')->setColumns(6);
        yield VersionField::new('latest')->setColumns(6);
        yield AdvisoryCountField::new('advisoryCount')->onlyOnIndex()->setLabel('Adv.')->setCssClass('text-center');
        yield UrlField::new('packagistUrl')->setColumns(6)->hideOnIndex();
        yield AssociationField::new('installations')->setColumns(6);
        yield LatestStatusField::new('latestStatus')->setColumns(6);
        yield AssociationField::new('advisories')->onlyOnDetail();
        yield DateTimeField::new('createdAt')->hideOnIndex();
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('package')
            ->add('version')
            ->add('latest')
            ->add('latestStatus')
        ;
    }
}
