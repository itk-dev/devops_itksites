<?php

namespace App\Form\Type\Admin;

use App\Types\DetectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DetectionFilterType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => DetectionType::CHOICES,
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
