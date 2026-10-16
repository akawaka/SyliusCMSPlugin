<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Form\Type\Block;

use Akawaka\SyliusCMSPlugin\Enum\Alignment;
use Akawaka\SyliusCMSPlugin\Enum\ButtonStyle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ButtonBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, ['label' => 'akawaka_cms.block.button.text', 'constraints' => [new NotBlank()]])
            ->add('url', TextType::class, ['label' => 'akawaka_cms.block.button.url', 'constraints' => [new NotBlank()]])
            ->add('style', EnumType::class, [
                'label' => 'akawaka_cms.block.button.style',
                'class' => ButtonStyle::class,
                'choice_label' => static fn (ButtonStyle $style): string => $style->label(),
            ])
            ->add('alignment', EnumType::class, [
                'label' => 'akawaka_cms.block.alignment',
                'class' => Alignment::class,
                'choice_label' => static fn (Alignment $alignment): string => $alignment->label(),
            ])
        ;
    }
}
