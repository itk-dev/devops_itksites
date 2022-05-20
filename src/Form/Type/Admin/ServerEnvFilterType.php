<?php

namespace App\Form\Type\Admin;

use App\Types\ServerEnvType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServerEnvFilterType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => ServerEnvType::CHOICES,
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
