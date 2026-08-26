<?php

namespace App\Controller\Admin;

use App\Entity\SecurityContract;
use App\Service\ServiceAgreementSyncService;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Translation\TranslatableMessage;

class SecurityContractCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly ServiceAgreementSyncService $syncService,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return SecurityContract::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['project.name' => 'ASC'])
            ->setSearchFields(['project.name', 'hostingProvider', 'serverSize'])
            ->showEntityActionsInlined()
            ->setPageTitle(Crud::PAGE_INDEX, 'Service Agreements')
            ->setHelp(Crud::PAGE_INDEX, 'Service agreements are synced from Economics. Click "Sync all" to update.');
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::DELETE, Action::NEW, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $this->createSyncAllAction());
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Project');
        yield BooleanField::new('active')->renderAsSwitch(false)->setColumns(2);
        yield BooleanField::new('eol')->setLabel('EOL')->renderAsSwitch(false)->setColumns(2);
        yield TextField::new('project.name')->setLabel('Project')->setColumns(8);
        yield TextField::new('project.leantimeId')->setLabel('Leantime ID')->hideOnIndex();
        yield TextField::new('projectGitRepos')->setLabel('GitHub repos')->hideOnIndex();
        yield TextField::new('hostingProvider');

        yield FormField::addFieldset('Links');
        yield UrlField::new('project.leantimeUrl')->setLabel('Leantime URL')->hideOnIndex();
        yield UrlField::new('documentUrl')->setLabel('Document URL')->hideOnIndex();

        yield FormField::addFieldset('Contact');
        yield TextField::new('clientContactName')->hideOnIndex();
        yield TextField::new('clientContactEmail')->hideOnIndex();

        yield FormField::addFieldset('Budget');
        // The amount is Danish kroner, which the admin never said anywhere.
        // 5.5's prepend()/append() addons would be the way to show a unit inside
        // an input, but they render on form pages only and this CRUD disables
        // NEW and EDIT (see configureActions), so index and detail are the only
        // pages it has. Hence formatting instead of an addon.
        yield NumberField::new('monthlyPrice')->setTextAlign('right')->setColumns(6)->formatValue(self::formatKroner(...));

        yield FormField::addFieldset('Infrastructure');
        yield BooleanField::new('dedicatedServer')->renderAsSwitch(false)->hideOnIndex();
        yield TextField::new('serverSize')->hideOnIndex();

        yield FormField::addFieldset('Validity');
        yield DateField::new('validFrom')->setColumns(6);
        yield DateField::new('validTo')->setColumns(6);
    }

    /**
     * An amount as Danish kroner: 12.500,50 kr.
     *
     * Through Intl rather than by pasting a suffix on, so the grouping and the
     * decimal separator are Danish too. The application locale is `en`, which
     * would otherwise render 12,500.5 with no currency at all.
     */
    public static function formatKroner(?float $value): ?string
    {
        if (null === $value) {
            return null;
        }

        return (new \NumberFormatter('da_DK', \NumberFormatter::CURRENCY))->formatCurrency($value, 'DKK') ?: null;
    }

    /**
     * The attribute is what makes this method reachable as a CRUD action.
     *
     * Without it EasyAdmin throws while rendering the "Sync all" button, which
     * took the whole index page with it — see the "Custom CRUD Actions" section
     * of the bundle's UPGRADE.md.
     */
    #[AdminRoute]
    public function syncAll(): RedirectResponse
    {
        try {
            $result = $this->syncService->syncAll();

            $this->addFlash('info', sprintf('Synced %d projects.', $result['projects']));

            if (!empty($result['unmatchedRepoNames'])) {
                $this->addFlash('warning', sprintf(
                    'Could not link %d GitHub repo name(s) to existing GitRepo entries: %s',
                    count($result['unmatchedRepoNames']),
                    implode(', ', $result['unmatchedRepoNames']),
                ));
            }
        } catch (\Throwable $e) {
            $this->addFlash('error', sprintf('An error occurred while syncing: %s', $e->getMessage()));
        }

        return $this->redirect(
            $this->adminUrlGenerator
                ->unsetAll()
                ->setController(static::class)
                ->setAction(Crud::PAGE_INDEX)
                ->generateUrl()
        );
    }

    private function createSyncAllAction(): Action
    {
        return Action::new('syncAll', new TranslatableMessage('Sync all'), 'fa fa-rotate')
            ->createAsGlobalAction()
            ->linkToCrudAction('syncAll')
            ->setHtmlAttributes([
                'onclick' => "const i=this.querySelector('i');if(i){i.classList.add('fa-spin')}this.style.pointerEvents='none';this.style.opacity='0.6'",
            ]);
    }
}
