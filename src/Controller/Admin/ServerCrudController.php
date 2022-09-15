<?php

namespace App\Controller\Admin;

use App\Entity\Server;
use App\Form\Type\Admin\HostingProviderFilter;
use App\Form\Type\Admin\MariaDbVersionFilter;
use App\Form\Type\Admin\ServerTypeFilter;
use App\Form\Type\Admin\SystemFilter;
use App\Types\DatabaseVersionType;
use App\Types\HostingProviderType;
use App\Types\ServerTypeType;
use App\Types\SystemType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\RequestStack;

class ServerCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Server::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        if ($this->requestStack->getSession()->remove('confetti')) {
            $crud->overrideTemplate('layout', 'EasyAdminBundle/layout.html.twig');
        }

        $crud->showEntityActionsInlined();

        return $crud;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addPanel('Provider Details');
        yield TextField::new('name')->setColumns(8)->setTemplatePath('EasyAdminBundle/Fields/ssh_link.html.twig');
        yield TextField::new('hostingProviderName')->setColumns(4)->hideOnIndex();
        yield ChoiceField::new('type')->setChoices(ServerTypeType::CHOICES)->renderExpanded()->setColumns(8)->setTemplatePath('EasyAdminBundle/Fields/server.html.twig');
        yield ChoiceField::new('hostingProvider')->setChoices(HostingProviderType::CHOICES)->setColumns(4)->setTemplatePath('EasyAdminBundle/Fields/hosting_provider.html.twig')->setLabel('Host. Pro.');
        yield TextField::new('apiKey')->setColumns(8)->setFormTypeOptions(['disabled' => 'true'])->onlyOnDetail()->setTemplatePath('EasyAdminBundle/Fields/code.html.twig');
        yield FormField::addPanel('Network');
        yield TextField::new('internalIp')->setColumns(6)->setTemplatePath('EasyAdminBundle/Fields/code.html.twig');
        yield TextField::new('externalIp')->setColumns(6)->setTemplatePath('EasyAdminBundle/Fields/code.html.twig');
        yield FormField::addPanel('Options');
        yield BooleanField::new('aarhusSsl')->setColumns(3)->hideOnIndex();
        yield BooleanField::new('letsEncryptSsl')->setColumns(3)->hideOnIndex();
        yield BooleanField::new('veeam')->setColumns(2)->hideOnIndex();
        yield BooleanField::new('azureBackup')->setColumns(2)->hideOnIndex();
        yield BooleanField::new('monitoring')->setColumns(2)->hideOnIndex();
        yield FormField::addPanel('System Details');
        yield ChoiceField::new('databaseVersion')->setChoices(DatabaseVersionType::CHOICES)->renderExpanded()->setColumns(6)->setTemplatePath('EasyAdminBundle/Fields/db_version.html.twig')->setLabel('DB ver.');
        yield ChoiceField::new('system')->setChoices(SystemType::CHOICES)->renderExpanded()->setColumns(6)->setTemplatePath('EasyAdminBundle/Fields/version.html.twig');
        yield FormField::addPanel('Miscellaneous');
        yield TextField::new('serviceDeskTicket')->setColumns(12)->hideOnIndex();
        yield TextareaField::new('note')->hideOnIndex()->setColumns(6);
        yield TextareaField::new('usedFor')->hideOnIndex()->setColumns(6);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add(HostingProviderFilter::new('hostingProvider'))
            ->add(MariaDbVersionFilter::new('databaseVersion'))
            ->add(ServerTypeFilter::new('type'))
            ->add(SystemFilter::new('system'))
        ;
    }
}
