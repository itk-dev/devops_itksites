<?php

namespace App\Controller\Admin;

use App\Entity\Server;
use App\Types\HostingProviderType;
use App\Types\MariaDbVersionType;
use App\Types\SystemType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ServerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Server::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name')->setColumns(8);
        yield TextField::new('hostingProviderName')->setColumns(4);
        yield TextField::new('apiKey')->setColumns(8)->hideOnIndex()->setFormTypeOptions(['disabled' => 'true']);
        yield ChoiceField::new('hostingProvider')->setChoices(HostingProviderType::CHOICES)->setColumns(4);
        yield TextField::new('internalIp')->setColumns(6);
        yield TextField::new('externalIp')->setColumns(6);
        yield BooleanField::new('aarhusSsl')->setColumns(3)->hideOnIndex();
        yield BooleanField::new('letsEncryptSsl')->setColumns(3)->hideOnIndex();
        yield BooleanField::new('veeam')->setColumns(2)->hideOnIndex();
        yield BooleanField::new('azureBackup')->setColumns(2)->hideOnIndex();
        yield BooleanField::new('monitoring')->setColumns(2)->hideOnIndex();
        yield ChoiceField::new('databaseVersion')->setChoices(MariaDbVersionType::CHOICES)->renderExpanded()->setColumns(6);
        yield ChoiceField::new('system')->setChoices(SystemType::CHOICES)->renderExpanded()->setColumns(6);
        yield TextField::new('serviceDeskTicket')->setColumns(12)->hideOnIndex();
        yield TextareaField::new('note')->hideOnIndex()->setColumns(6);
        yield TextareaField::new('usedFor')->hideOnIndex()->setColumns(6);
    }
}
