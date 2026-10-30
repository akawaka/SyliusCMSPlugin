<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Form\Type\Block;

use AKAWAKA\SyliusCMSPlugin\Enum\Alignment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class BannerBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'akawaka_cms.block.banner.title', 'required' => false])
            ->add('subtitle', TextType::class, ['label' => 'akawaka_cms.block.banner.subtitle', 'required' => false])
            ->add('image', TextType::class, ['label' => 'akawaka_cms.block.banner.image', 'required' => false])
            ->add('buttonLabel', TextType::class, ['label' => 'akawaka_cms.block.banner.button_label', 'required' => false])
            ->add('buttonUrl', TextType::class, ['label' => 'akawaka_cms.block.banner.button_url', 'required' => false])
            ->add('alignment', EnumType::class, [
                'label' => 'akawaka_cms.block.alignment',
                'class' => Alignment::class,
                'choice_label' => static fn (Alignment $alignment): string => $alignment->label(),
            ])
        ;
    }
}
