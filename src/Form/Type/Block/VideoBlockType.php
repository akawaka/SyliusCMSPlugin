<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Form\Type\Block;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class VideoBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('url', TextType::class, ['label' => 'akawaka_cms.block.video.url', 'constraints' => [new NotBlank()]])
            ->add('autoplay', CheckboxType::class, ['label' => 'akawaka_cms.block.video.autoplay', 'required' => false])
        ;
    }
}
