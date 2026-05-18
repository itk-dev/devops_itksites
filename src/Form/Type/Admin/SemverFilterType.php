<?php

declare(strict_types=1);

namespace App\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SemverFilterType extends AbstractType
{
    public const string COMPARISON_EQ = '=';
    public const string COMPARISON_NEQ = '!=';
    public const string COMPARISON_GT = '>';
    public const string COMPARISON_GTE = '>=';
    public const string COMPARISON_LT = '<';
    public const string COMPARISON_LTE = '<=';
    public const string COMPARISON_BETWEEN = 'between';
    public const string COMPARISON_BETWEEN_EXCLUSIVE = 'between_exclusive';

    public const array COMPARISON_CHOICES = [
        '=' => self::COMPARISON_EQ,
        '!=' => self::COMPARISON_NEQ,
        '>' => self::COMPARISON_GT,
        '>=' => self::COMPARISON_GTE,
        '<' => self::COMPARISON_LT,
        '<=' => self::COMPARISON_LTE,
        'between (inclusive)' => self::COMPARISON_BETWEEN,
        'between (exclusive)' => self::COMPARISON_BETWEEN_EXCLUSIVE,
    ];

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('comparison', ChoiceType::class, [
                'choices' => self::COMPARISON_CHOICES,
                'choice_translation_domain' => false,
            ])
            ->add('value', TextType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'e.g. 10.0.0'],
            ])
            ->add('value2', TextType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'upper bound (optional, makes it a range)'],
            ])
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
        ]);
    }
}
