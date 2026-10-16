<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Form\Type\Block;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

final class RichTextBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('html', TextareaType::class, ['label' => 'akawaka_cms.block.rich_text.html', 'required' => false]);
    }
}
