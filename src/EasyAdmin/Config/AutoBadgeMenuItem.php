<?php

namespace App\EasyAdmin\Config;

use App\EasyAdmin\Config\Menu\AutoBadgeCrudMenuItem;
use Symfony\Contracts\Translation\TranslatableInterface;

class AutoBadgeMenuItem
{
    /** @phpstan-ignore missingType.return  */
    public function __call(string $name, array $arguments)
    {
        throw new \BadMethodCallException(sprintf('Method %s not implemented', $name));
    }

    /** @phpstan-ignore missingType.return  */
    public static function __callStatic(string $name, array $arguments)
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
