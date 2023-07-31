<?php

namespace App\Controller\Admin;

use App\Entity\OIDC;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Component\Translation\TranslatableMessage;

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
        yield UrlField::new('site');
        yield DateTimeField::new('expirationTime');
        yield UrlField::new('onePasswordUrl')
            ->setLabel(new TranslatableMessage('1Password url'));
    }
}
