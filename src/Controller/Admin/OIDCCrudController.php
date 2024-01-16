<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\OIDC;
use App\Repository\SiteRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Component\Translation\TranslatableMessage;

class OIDCCrudController extends AbstractCrudController
{
    public function __construct(private readonly SiteRepository $siteRepository)
    {
    }

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
            ->setHelp(new TranslatableMessage('Tell where to find documentation on how OpenID Connect is used on the site and
how to configure the use.'));
        yield DateField::new('expirationTime')->setFormat('yyyy-MM-dd')->setLabel('Expiration Date');

        yield TextareaField::new('notes');
    }
}
