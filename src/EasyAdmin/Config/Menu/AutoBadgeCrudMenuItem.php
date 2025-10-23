<?php

namespace App\EasyAdmin\Config\Menu;

use EasyCorp\Bundle\EasyAdminBundle\Config\Menu\CrudMenuItem;

class AutoBadgeCrudMenuItem
{
    private CrudMenuItem $crudMenuItem;

    public function __construct(...$args)
    {
        $this->crudMenuItem = new CrudMenuItem(...$args);
    }

    public function __call($name, $arguments)
    {
        return $this->crudMenuItem->$name(...$arguments);
    }

    public static function __callStatic(string $name, array $arguments)
    {
        throw new \BadMethodCallException(sprintf('Static method %s not implemented', $name));
    }

    public function setBadge(/* \Stringable|string|int|float|bool|null */ $content, string $style = 'secondary', array $htmlAttributes = []): self
    {
        if (!is_int($content)) {
            throw new \InvalidArgumentException('The badge content must be an integer');
        }

        if ($content > 0) {
            $this->crudMenuItem->setBadge($content, $style, $htmlAttributes);
        }

        return $this;
    }
}