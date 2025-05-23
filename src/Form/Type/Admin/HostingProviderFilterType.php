<?php

declare(strict_types=1);

namespace App\Form\Type\Admin;

use App\Types\HostingProviderType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HostingProviderFilterType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => HostingProviderType::CHOICES,
        ]);
    }

    #[\Override]
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
