<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Form\Type\Block;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Range;

final class ProductGridBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('taxon', TextType::class, ['label' => 'akawaka_cms.block.product_grid.taxon', 'required' => false])
            ->add('limit', IntegerType::class, ['label' => 'akawaka_cms.block.product_grid.limit', 'constraints' => [new Range(min: 1, max: 24)]])
            ->add('columns', ChoiceType::class, [
                'label' => 'akawaka_cms.block.product_grid.columns',
                'choices' => [2 => 2, 3 => 3, 4 => 4, 6 => 6],
            ])
        ;
    }
}
