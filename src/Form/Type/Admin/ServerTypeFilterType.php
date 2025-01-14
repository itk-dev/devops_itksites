<?php

declare(strict_types=1);

namespace App\Form\Type\Admin;

use App\Types\ServerTypeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServerTypeFilterType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => ServerTypeType::CHOICES,
        ]);
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
