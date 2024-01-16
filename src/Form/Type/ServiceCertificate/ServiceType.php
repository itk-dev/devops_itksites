<?php

declare(strict_types=1);

namespace App\Form\Type\ServiceCertificate;

use App\Entity\ServiceCertificate\Service;
use App\Form\Type\Admin\TextAutocompleteType;
use App\Repository\ServiceCertificate\ServiceRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

class ServiceType extends AbstractType
{
    public function __construct(private ServiceRepository $serviceRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', TextAutocompleteType::class, [
                'tom_select_options' => [
                    // @see https://tom-select.js.org/docs/#general-configuration
                    'options' => array_map(static fn ($type) => ['value' => $type, 'text' => $type], $options['types']),
                    'create' => true,
                    'maxItems' => 1,
                    'persist' => false,
                ],
                'help' => new TranslatableMessage('Select an existing type or create a new one.'),
            ])
            ->add('onePasswordUrl', UrlType::class, [
                'label' => new TranslatableMessage('1Password url'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Service::class,
                'types' => $this->serviceRepository->getTypes(),
            ])
            ->setRequired('types')
            ->setAllowedTypes('types', 'string[]')
        ;
    }
}
