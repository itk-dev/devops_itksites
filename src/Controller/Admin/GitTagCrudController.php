<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Admin\Field\VersionField;
use App\Entity\GitTag;
use App\Form\Type\Admin\SemverFilter;
use App\Trait\SemverSortableCrudControllerTrait;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class GitTagCrudController extends AbstractCrudController
{
    use SemverSortableCrudControllerTrait;

    public static function getEntityFqcn(): string
    {
        return GitTag::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setDefaultSort(['repo.organization' => 'ASC', 'repo.repo' => 'ASC'])
        ;
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::DELETE, Action::NEW, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('repo')->setColumns(6);
        yield AssociationField::new('installations')->setColumns(6);
        yield VersionField::new('tag')->setColumns(6);
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('repo')
            ->add(SemverFilter::new('tag'))
        ;
    }

    #[\Override]
    protected function semverSortedProperties(): array
    {
        return ['tag'];
    }
}
