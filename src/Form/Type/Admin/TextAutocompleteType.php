<?php

declare(strict_types=1);

namespace App\Form\Type\Admin;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TextAutocompleteType extends TextType
{
    public function getBlockPrefix(): string
    {
        return 'textautocomplete';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver
            // Heavily inspired by https://symfony.com/bundles/ux-autocomplete/current/index.html#extending-tom-select
            ->setDefault('tom_select_options', []);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars += [
            'tom_select_options' => $options['tom_select_options'],
        ];
    }
}
