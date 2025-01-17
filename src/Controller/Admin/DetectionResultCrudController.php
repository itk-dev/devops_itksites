<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Admin\Field\RootDirField;
use App\Admin\Field\ServerTypeField;
use App\Admin\Field\TextMonospaceField;
use App\Admin\Field\VersionField;
use App\Entity\DetectionResult;
use App\Form\Type\Admin\DetectionFilter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

class DetectionResultCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DetectionResult::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud->showEntityActionsInlined()->setDefaultSort(['lastContact' => 'DESC']);
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
        yield TextMonospaceField::new('id')->hideOnIndex();
        yield VersionField::new('type')->setColumns(4);
        yield RootDirField::new('rootDir')->setColumns(12);
        yield ServerTypeField::new('server.type')->setLabel('Type');
        yield AssociationField::new('server');
        yield DateTimeField::new('createdAt')->hideOnIndex();
        yield DateTimeField::new('modifiedAt')->hideOnIndex();
        yield DateTimeField::new('lastContact');
        yield CodeEditorField::new('prettyData')->hideOnIndex()->setLabel('Data');
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(DetectionFilter::new('type'))
            ->add('rootDir')
            ->add('server')
            ->add('lastContact')
        ;
    }
}
