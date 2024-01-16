<?php

declare(strict_types=1);

namespace App\Trait;

use App\Service\Exporter;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use Symfony\Component\Translation\TranslatableMessage;

trait ExportCrudControllerTrait
{
    private Exporter $exporter;

    protected function setExporter(Exporter $exporter)
    {
        $this->exporter = $exporter;
    }

    protected function createExportAction(string|TranslatableMessage $label = null): Action
    {
        return Action::new('export', $label ?? new TranslatableMessage('Export'))
            ->createAsGlobalAction()
            ->linkToCrudAction('export');
    }

    public function export(AdminContext $context)
    {
        if (!isset($this->exporter)) {
            throw new \RuntimeException(sprintf('Exporter not set in %s', static::class));
        }

        assert($this instanceof AbstractCrudController);
        // Lifted from self::index().
        $fields = FieldCollection::new($this->configureFields(Crud::PAGE_INDEX));
        $context->getCrud()->setFieldAssets($this->getFieldAssets($fields));
        $filters = $this->container->get(FilterFactory::class)->create($context->getCrud()->getFiltersConfig(), $fields,
            $context->getEntity());
        $queryBuilder = $this->createIndexQueryBuilder($context->getSearch(), $context->getEntity(), $fields, $filters);

        return $this->exporter->export($queryBuilder->getQuery(), static::getEntityFqcn());
    }
}
