<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Form\Type;

use Akawaka\SyliusCMSPlugin\Form\DataTransformer\BlocksJsonTransformer;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

final class ContentBlockTranslationType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('blocks', TextareaType::class, [
            'label' => 'akawaka_cms.form.page.blocks',
            'required' => false,
        ]);

        $builder->get('blocks')->addModelTransformer(new BlocksJsonTransformer());
    }

    public function getBlockPrefix(): string
    {
        return 'akawaka_cms_content_block_translation';
    }
}
