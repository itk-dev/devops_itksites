<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\AbstractBaseEntity;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Registry\AdminControllerRegistryInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Twig\Attribute\AsTwigFunction;

class AppExtension
{
    public function __construct(private readonly AdminUrlGenerator $adminUrlGenerator, private readonly AdminControllerRegistryInterface $crudControllerRegistry)
    {
    }

    #[AsTwigFunction(name: 'has_display')]
    public function hasDisplay(AbstractBaseEntity $entity): bool
    {
        return method_exists($entity, 'display');
    }

    #[AsTwigFunction(name: 'entity_display')]
    public function entityDisplay(AbstractBaseEntity $entity, string $action): string
    {
        if (method_exists($entity, 'display')) {
            if ('detail' == $action) {
                return $entity->display(1);
            }

            return $entity->display(0);
        }

        return $entity->__toString();
    }

    #[AsTwigFunction(name: 'admin_detail_url')]
    public function url(AbstractBaseEntity $entity): string
    {
        $crudController = $this->crudControllerRegistry->findCrudControllerByEntity($entity::class);
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
