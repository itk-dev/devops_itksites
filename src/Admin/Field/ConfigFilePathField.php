<?php

namespace App\Admin\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;

class ConfigFilePathField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, string $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)

            // this template is used in 'index' and 'detail' pages
            ->setTemplatePath('EasyAdminBundle/Fields/config_file_path.html.twig');
    }
}
