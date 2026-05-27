<?php

namespace App\EasyAdmin\Config;

use App\EasyAdmin\Config\Menu\AutoBadgeCrudMenuItem;
use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * Factory that mirrors EasyAdmin's MenuItem static API but produces
 * AutoBadgeCrudMenuItem so dashboard entries hide their badge when the count is zero.
 */
class AutoBadgeMenuItem
{
    public function __call(string $name, array $arguments): never
    {
        throw new \BadMethodCallException(sprintf('Method %s not implemented', $name));
    }

    public static function __callStatic(string $name, array $arguments): never
    {
        throw new \BadMethodCallException(sprintf('Static method %s not implemented', $name));
    }

    /**
     * @param string|null $icon The full CSS classes of the FontAwesome icon to render (see https://fontawesome.com/v6/search?m=free)
     */
    public static function linkToCrud(TranslatableInterface|string $label, ?string $icon, string $entityFqcn): AutoBadgeCrudMenuItem
    {
        return new AutoBadgeCrudMenuItem($label, $icon, $entityFqcn);
    }
}
