<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Admin\AdvisoryCountSort;
use App\Admin\Field\AdvisoryCountField;
use App\Admin\Field\ConfigFilePathField;
use App\Admin\Field\DomainField;
use App\Admin\Field\RootDirField;
use App\Admin\Field\ServerTypeField;
use App\Admin\Field\SiteTypeField;
use App\Admin\Field\VersionField;
use App\Entity\Site;
use App\Form\Type\Admin\SemverFilter;
use App\Form\Type\Admin\ServerTypeFilter;
use App\Trait\ExportCrudControllerTrait;
use App\Trait\SemverSortableCrudControllerTrait;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

/**
 * @extends AbstractCrudController<Site>
 */
class SiteCrudController extends AbstractCrudController
{
    /** @use ExportCrudControllerTrait<Site> */
    use ExportCrudControllerTrait;
    // Aliased so this controller can add its own step after the semver rewrite.
    use SemverSortableCrudControllerTrait {
        createIndexQueryBuilder as private semverIndexQueryBuilder;
    }

    public function __construct()
    {
    }

    public static function getEntityFqcn(): string
    {
        return Site::class;
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
        yield DomainField::new('primaryDomain')->setColumns(12);
        yield AdvisoryCountField::new('advisoryCount')->setLabel('Adv.')->setSortable(true);
        yield AssociationField::new('domains')->hideOnIndex();
        yield SiteTypeField::new('type')->setLabel('Stack');
        yield ConfigFilePathField::new('configFilePath')->setColumns(12)->hideOnIndex();
        yield RootDirField::new('rootDir')->setColumns(12)->hideOnIndex();
        yield VersionField::new('phpVersion')->setLabel('PHP');
        yield AssociationField::new('installation')->hideOnIndex();
        yield ServerTypeField::new('server.type')->setLabel('Type')->setSortable(true);
        yield AssociationField::new('server');
        yield AssociationField::new('detectionResult')->hideOnIndex();
        yield DateTimeField::new('createdAt')->hideOnIndex();
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('primaryDomain')
            ->add('configFilePath')
            ->add(SemverFilter::new('phpVersion', 'PHP'))
            ->add('server')
            ->add(ServerTypeFilter::new('server.type', 'Server type'));
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = $this->semverIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        AdvisoryCountSort::apply($qb, $searchDto);

        return $qb;
    }

    #[\Override]
    protected function semverSortedProperties(): array
    {
        return ['phpVersion'];
    }
}
