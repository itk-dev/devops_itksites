<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Admin\Field\ConfigFilePathField;
use App\Admin\Field\DomainField;
use App\Admin\Field\RootDirField;
use App\Admin\Field\ServerTypeField;
use App\Admin\Field\SiteTypeField;
use App\Admin\Field\VersionField;
use App\Entity\Site;
use App\Service\Exporter;
use App\Trait\ExportCrudControllerTrait;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

class SiteCrudController extends AbstractCrudController
{
    use ExportCrudControllerTrait;

    public function __construct(Exporter $exporter)
    {
        $this->setExporter($exporter);
    }

    public static function getEntityFqcn(): string
    {
        return Site::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $this->createExportAction())
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        yield DomainField::new('primaryDomain')->setColumns(12);
        yield AssociationField::new('domains')->hideOnIndex();
        yield SiteTypeField::new('type')->setLabel('Stack');
        yield ConfigFilePathField::new('configFilePath')->setColumns(12)->hideOnIndex();
        yield RootDirField::new('rootDir')->setColumns(12)->hideOnIndex();
        yield VersionField::new('phpVersion')->setLabel('PHP');
        yield AssociationField::new('installation')->hideOnIndex();
        yield ServerTypeField::new('server.type')->setLabel('Type');
        yield AssociationField::new('server');
        yield AssociationField::new('detectionResult')->hideOnIndex();
        yield DateTimeField::new('createdAt')->hideOnIndex();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('primaryDomain')
            ->add('configFilePath')
            ->add('phpVersion')
            ->add('server');
    }
}
