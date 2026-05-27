<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Admin\Field\DomainField;
use App\Admin\Field\ServerTypeField;
use App\Admin\Field\SiteTypeField;
use App\Entity\Domain;
use App\Form\Type\Admin\ServerTypeFilter;
use App\Trait\ExportCrudControllerTrait;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

class DomainCrudController extends AbstractCrudController
{
    use ExportCrudControllerTrait;

    public static function getEntityFqcn(): string
    {
        return Domain::class;
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
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $this->createExportAction());
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield DomainField::new('address')->setColumns(12);
        yield SiteTypeField::new('site.type')->hideOnIndex();
        yield AssociationField::new('site')->hideOnIndex();
        yield ServerTypeField::new('server.type')->setLabel('Type')->setSortable(true);
        yield AssociationField::new('server');
        yield AssociationField::new('detectionResult')->hideOnIndex();
        yield DateTimeField::new('createdAt')->hideOnIndex();
        yield DateTimeField::new('detectionResult.lastContact')->hideOnIndex();
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('address')
            ->add('site')
            ->add('server')
            ->add(ServerTypeFilter::new('server.type', 'Server type'))
        ;
    }
}
