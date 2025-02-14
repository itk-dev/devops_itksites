<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Server;
use App\Form\Type\Admin\HostingProviderFilter;
use App\Form\Type\Admin\MariaDbVersionFilter;
use App\Form\Type\Admin\ServerTypeFilter;
use App\Form\Type\Admin\SystemFilter;
use App\Service\Exporter;
use App\Trait\ExportCrudControllerTrait;
use App\Types\DatabaseVersionType;
use App\Types\HostingProviderType;
use App\Types\ServerTypeType;
use App\Types\SystemType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\RequestStack;

class ServerCrudController extends AbstractCrudController
{
    use ExportCrudControllerTrait;

    public function __construct(
        Exporter $exporter,
        private readonly RequestStack $requestStack,
    ) {
        $this->setExporter($exporter);
    }

    public static function getEntityFqcn(): string
    {
        return Server::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        if ($this->requestStack->getSession()->remove('confetti')) {
            $crud->overrideTemplate('layout', 'EasyAdminBundle/layout.html.twig');
        }

        //        $crud->showEntityActionsInlined();

        return $crud;
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $this->createExportAction())
        ;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Provider Details');
        yield TextField::new('name')->setColumns(8)->setTemplatePath('EasyAdminBundle/Fields/ssh_link.html.twig');
        yield TextField::new('hostingProviderName')->setColumns(4)->hideOnIndex();
        yield AssociationField::new('installations');
        yield ChoiceField::new('type')->setChoices(ServerTypeType::CHOICES)->renderExpanded()->setColumns(8)->setTemplatePath('EasyAdminBundle/Fields/server_type.html.twig');
        yield ChoiceField::new('hostingProvider')->setChoices(HostingProviderType::CHOICES)->setColumns(4)->setTemplatePath('EasyAdminBundle/Fields/hosting_provider.html.twig')->setLabel('Host. Pro.');
        yield TextField::new('apiKey')->setColumns(8)->setFormTypeOptions(['disabled' => 'true'])->onlyOnDetail()->setTemplatePath('EasyAdminBundle/Fields/code.html.twig');
        yield FormField::addFieldset('Network');
        yield TextField::new('internalIp')->setColumns(6)->setTemplatePath('EasyAdminBundle/Fields/code.html.twig')->hideOnIndex();
        yield TextField::new('externalIp')->setColumns(6)->setTemplatePath('EasyAdminBundle/Fields/code.html.twig')->hideOnIndex();
        yield FormField::addFieldset('Options');
        yield BooleanField::new('aarhusSsl')->setColumns(3)->hideOnIndex();
        yield BooleanField::new('letsEncryptSsl')->setColumns(3)->hideOnIndex();
        yield BooleanField::new('veeam')->setColumns(2)->hideOnIndex();
        yield BooleanField::new('azureBackup')->setColumns(2)->hideOnIndex();
        yield BooleanField::new('monitoring')->setColumns(2)->hideOnIndex();
        yield FormField::addFieldset('System Details');
        yield ChoiceField::new('databaseVersion')->setChoices(DatabaseVersionType::CHOICES)->renderExpanded()->setColumns(6)->setTemplatePath('EasyAdminBundle/Fields/db_version.html.twig')->setLabel('DB ver.');
        yield ChoiceField::new('system')->setChoices(SystemType::CHOICES)->renderExpanded()->setColumns(6)->setTemplatePath('EasyAdminBundle/Fields/version.html.twig');
        yield FormField::addFieldset('Miscellaneous');
        yield TextField::new('serviceDeskTicket')->setColumns(12)->hideOnIndex();
        yield TextareaField::new('note')->hideOnIndex()->setColumns(6);
        yield TextareaField::new('usedFor')->hideOnIndex()->setColumns(6);
        yield DateTimeField::new('lastContactAt')->hideOnForm();
    }

    #[\Override]
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
