<?php

namespace App\EasyAdmin\Config\Menu;

use EasyCorp\Bundle\EasyAdminBundle\Config\Menu\CrudMenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Menu\MenuItemInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\MenuItemDto;
use Symfony\Contracts\Translation\TranslatableInterface;

class AutoBadgeCrudMenuItem implements MenuItemInterface
{
    private CrudMenuItem $crudMenuItem;

    public function __construct(TranslatableInterface|string $label, ?string $icon, string $entityFqcn)
    {
        $this->crudMenuItem = new CrudMenuItem($label, $icon, $entityFqcn);
    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->crudMenuItem->$name(...$arguments);
    }

    public static function __callStatic(string $name, array $arguments): never
    {
        throw new \BadMethodCallException(sprintf('Static method %s not implemented', $name));
    }

    public function setBadge(\Stringable|string|int|float|bool|null $content, string $style = 'secondary', array $htmlAttributes = []): self
    {
        if (!is_int($content)) {
            throw new \InvalidArgumentException('The badge content must be an integer');
        }

        if ($content > 0) {
            $this->crudMenuItem->setBadge($content, $style, $htmlAttributes);
        }

        return $this;
    }

    public function getAsDto(): MenuItemDto
    {
        return $this->crudMenuItem->getAsDto();
    }
}
