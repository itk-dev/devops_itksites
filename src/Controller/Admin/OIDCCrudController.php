<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\OIDC;
use App\Repository\SiteRepository;
use App\Trait\DeprecatedCrudControllerTrait;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @deprecated Removed from the admin menu. Kept so existing OIDC rows stay
 *             reachable by URL until the entity itself goes away.
 */
class OIDCCrudController extends AbstractFullCrudController
{
    use DeprecatedCrudControllerTrait;

    public function __construct(
        private readonly SiteRepository $siteRepository)
    {
    }

    public static function getEntityFqcn(): string
    {
        return OIDC::class;
    }

    #[\Override]
    protected function getDeprecationNotice(): string
    {
        return 'OIDC registrations are deprecated and no longer maintained here. This page is only reachable by direct link, so that existing entries stay readable. Do not add new ones.';
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        // Select domain from existing server primary domains on forms.
        if (in_array($pageName, [Crud::PAGE_NEW, Crud::PAGE_EDIT], true)) {
            $domains = $this->siteRepository->getPrimaryDomains();
            yield ChoiceField::new('domain')
                ->setChoices(array_combine($domains, $domains))
                ->setLabel('Site');
        } else {
            yield TextField::new('domain')
                ->setTemplatePath('admin/domain.html.twig')
                ->setLabel('Site');
        }

        yield UrlField::new('onePasswordUrl')
            ->setLabel(new TranslatableMessage('1Password url'));
        yield UrlField::new('usageDocumentationUrl')->hideOnIndex()
            ->setHelp(new TranslatableMessage('Tell where to find documentation on how OpenID Connect is used on the site and how to configure the use.'));
        yield DateField::new('expirationTime')->setFormat('yyyy-MM-dd')->setLabel('Expiration Date');

        yield TextareaField::new('notes');
    }
}
