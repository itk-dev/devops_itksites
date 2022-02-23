<?php

namespace App\Controller\Admin;

use App\Entity\Server;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ServerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Server::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name')->setColumns(6),
            TextField::new('hostingProviderName')->setColumns(6),
            TextField::new('apiKey')->setColumns(12),
            TextField::new('internalIp')->setColumns(6),
            TextField::new('externalIp')->setColumns(6),
            BooleanField::new('veeam')->setColumns(4),
            BooleanField::new('azureBackup')->setColumns(4),
            BooleanField::new('monitoring')->setColumns(4),
            TextField::new('sshUser'),
            TextField::new('databaseVersion'),
            TextField::new('sslProvider'),
            TextField::new('system'),
            TextField::new('serviceDeskTicket'),
            TextareaField::new('note')->hideOnIndex(),
            TextareaField::new('usedFor')->hideOnIndex(),
        ];
    }
}
