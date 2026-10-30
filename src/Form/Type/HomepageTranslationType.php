<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Form\Type;

use AKAWAKA\SyliusCMSPlugin\Form\DataTransformer\BlocksJsonTransformer;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class HomepageTranslationType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('metaTitle', TextType::class, [
                'label' => 'akawaka_cms.form.page.meta_title',
                'help' => 'akawaka_cms.form.homepage.meta_title_help',
                'required' => false,
            ])
            ->add('metaDescription', TextareaType::class, [
                'label' => 'akawaka_cms.form.page.meta_description',
                'help' => 'akawaka_cms.form.page.meta_description_help',
                'required' => false,
            ])
            ->add('ogImage', TextType::class, [
                'label' => 'akawaka_cms.form.page.og_image',
                'help' => 'akawaka_cms.form.page.og_image_help',
                'required' => false,
            ])
            ->add('blocks', TextareaType::class, [
                'label' => 'akawaka_cms.form.page.blocks',
                'required' => false,
            ])
        ;

        $builder->get('blocks')->addModelTransformer(new BlocksJsonTransformer());
    }

    public function getBlockPrefix(): string
    {
        return 'akawaka_cms_homepage_translation';
    }
}
