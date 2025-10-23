<?php

namespace App\Controller\Admin;

use App\Entity\SecurityContract;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class SecurityContractCrudController extends AbstractFullCrudController
{
    public static function getEntityFqcn(): string
    {
        return SecurityContract::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['project.name' => 'ASC'])
            // ->setSearchFields(['name', 'details'])
            ->showEntityActionsInlined()
            ->setPageTitle(Crud::PAGE_INDEX, 'Cyber Security Contracts');
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Project');
        yield BooleanField::new('active')->renderAsSwitch(false)->setColumns(2);
        yield AssociationField::new('project')->setColumns(10);

        yield FormField::addFieldset('Links');
        yield UrlField::new('project.leantimeUrl')->setLabel('Leantime URL')->setDisabled()->hideOnIndex()->hideWhenCreating();
        yield UrlField::new('economicsReportUrl')->setLabel('Economics URL')->hideOnIndex()->setColumns(12);
        yield UrlField::new('operationalContractUrl')->setLabel('Contract URL')->hideOnIndex()->setColumns(12);

        yield FormField::addFieldset('Budget');
        yield NumberField::new('monthlyPrice')->setTextAlign('right')->setColumns(6);
        yield NumberField::new('quarterlyHours')->setTextAlign('right')->setColumns(6);

        yield FormField::addFieldset('Validity');
        yield DateField::new('validFrom')->setColumns(6);
        yield DateField::new('validTo')->setColumns(6);

        yield FormField::addFieldset('Notes');
        yield TextareaField::new('notes')->hideOnIndex()->setColumns(12);
    }
}
