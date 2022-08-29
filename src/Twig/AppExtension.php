<?php

namespace App\Twig;

use App\Entity\AbstractBaseEntity;
use EasyCorp\Bundle\EasyAdminBundle\Registry\CrudControllerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private AdminUrlGenerator $adminUrlGenerator;
    private CrudControllerRegistry $crudControllerRegistry;

    public function __construct(AdminUrlGenerator $adminUrlGenerator, CrudControllerRegistry $crudControllerRegistry)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->crudControllerRegistry = $crudControllerRegistry;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('admin_detail_url', [$this, 'url']),
            new TwigFunction('entity_display', [$this, 'entity_display']),
            new TwigFunction('has_display', [$this, 'has_display']),
        ];
    }

    public function has_display(AbstractBaseEntity $entity): bool
    {
        return method_exists($entity, 'display');
    }

    public function entity_display(AbstractBaseEntity $entity, string $action): string
    {
        if (method_exists($entity, 'display')) {
            if ('detail' == $action) {
                return $entity->display(1);
            } else {
                return $entity->display(0);
            }
        }

        return $entity->__toString();
    }

    public function url(AbstractBaseEntity $entity): string
    {
        $crudController = $this->crudControllerRegistry->findCrudFqcnByEntityFqcn($entity::class);
        $id = $entity->getId();
        if (null === $id || null === $crudController) {
            return '';
        }

        return $this->adminUrlGenerator->unsetAll()
            ->setController($crudController)
            ->setAction('detail')
            ->setEntityId($id)
            ->generateUrl()
            ;
    }
}
