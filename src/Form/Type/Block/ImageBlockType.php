<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Form\Type\Block;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ImageBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('src', TextType::class, ['label' => 'akawaka_cms.block.image.src', 'constraints' => [new NotBlank()]])
            ->add('alt', TextType::class, ['label' => 'akawaka_cms.block.image.alt', 'required' => false])
            ->add('link', TextType::class, ['label' => 'akawaka_cms.block.image.link', 'required' => false])
        ;
    }
}
