<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Form\Type\Block;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

final class ColumnsBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('columns', ChoiceType::class, [
            'label' => 'akawaka_cms.block.columns.columns',
            'choices' => [2 => 2, 3 => 3, 4 => 4],
        ]);
    }
}
