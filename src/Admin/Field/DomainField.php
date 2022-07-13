<?php

namespace App\Admin\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;

class DomainField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)

            ->setFormattedValue('test')

            // this template is used in 'index' and 'detail' pages
            ->setTemplatePath('EasyAdminBundle/Fields/domain.html.twig');
    }
}
