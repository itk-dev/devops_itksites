<?php

namespace App\Form\Type\Admin;

use App\Types\SystemType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SystemFilterType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => SystemType::CHOICES,
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
