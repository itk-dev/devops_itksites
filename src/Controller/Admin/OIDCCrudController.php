<?php

namespace App\Controller\Admin;

use App\Entity\OIDC;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class OIDCCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return OIDC::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('site');
        yield DateField::new('expirationDate');
        yield TextareaField::new('claims');
        yield TextareaField::new('ad')->setLabel('AD');
        yield TextField::new('discoveryUrl');
        yield TextField::new('graph');
    }
}
