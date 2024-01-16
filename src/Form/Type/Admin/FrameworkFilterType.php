<?php

declare(strict_types=1);

namespace App\Form\Type\Admin;

use App\Types\FrameworkTypes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FrameworkFilterType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => FrameworkTypes::CHOICES,
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
