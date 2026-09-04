<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ServiceCertificate;
use App\Form\Type\ServiceCertificate\ServiceType;
use App\Repository\SiteRepository;
use App\Trait\DeprecatedCrudControllerTrait;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @deprecated Removed from the admin menu. Kept so existing service certificate
 *             rows stay reachable by URL until the entity itself goes away.
 */
class ServiceCertificateCrudController extends AbstractFullCrudController
{
    use DeprecatedCrudControllerTrait;

    public function __construct(
        private readonly SiteRepository $siteRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return ServiceCertificate::class;
    }

    #[\Override]
    protected function getDeprecationNotice(): string
    {
        return 'Service certificates are deprecated and no longer maintained here. This page is only reachable by direct link, so that existing entries stay readable. Do not add new ones.';
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setEntityLabelInSingular('Service certificate')
            ->setEntityLabelInPlural('Service certificates')
            // @see https://symfony.com/bundles/EasyAdminBundle/current/design.html#form-field-templates
            ->setFormThemes(['admin/form.html.twig', '@EasyAdmin/crud/form_theme.html.twig'])
            ->setSearchFields(['domain', 'name', 'description', 'services.type']);
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        // Select domain from existing server primary domains on forms.
        if (in_array($pageName, [Crud::PAGE_NEW, Crud::PAGE_EDIT], true)) {
            $domains = $this->siteRepository->getPrimaryDomains();
            yield ChoiceField::new('domain')
                ->setChoices(array_combine($domains, $domains))
            ;
        } else {
            yield TextField::new('domain')
                ->setTemplatePath('admin/domain.html.twig');
        }

        yield TextField::new('name');
        yield TextEditorField::new('description')->onlyOnForms()
            ->setHelp(new TranslatableMessage('Tell what this certificate is used for.'));
        yield TextField::new('description')->onlyOnDetail()
            ->setHelp(new TranslatableMessage('Tell what this certificate is used for.'))->renderAsHtml();
        yield TextField::new('description')->onlyOnIndex()
            ->setHelp(new TranslatableMessage('Tell what this certificate is used for.'))->setMaxLength(33)->stripTags();
        yield UrlField::new('onePasswordUrl')
            ->setLabel(new TranslatableMessage('1Password url'))->hideOnIndex();
        yield UrlField::new('usageDocumentationUrl')->hideOnIndex()
            ->setHelp(new TranslatableMessage('Tell where to find documentation on how the certificate is used on the site and how to configure the use.'));
        yield DateTimeField::new('expirationTime');

        yield CollectionField::new('services')
            ->setEntryIsComplex()
            ->setEntryType(ServiceType::class)
            ->setFormTypeOptions([
                // Make sure that subform is validated.
                'required' => true,
            ])
            ->renderExpanded()
            ->setTemplatePath('service_certificate/services.html.twig')
        ;
    }
}
