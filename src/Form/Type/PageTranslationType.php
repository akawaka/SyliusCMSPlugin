<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class PageTranslationType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'akawaka_cms.form.page.title',
                'required' => false,
            ])
            ->add('slug', TextType::class, [
                'label' => 'akawaka_cms.form.page.slug',
                'required' => false,
            ])
            ->add('metaTitle', TextType::class, [
                'label' => 'akawaka_cms.form.page.meta_title',
                'required' => false,
            ])
            ->add('metaDescription', TextareaType::class, [
                'label' => 'akawaka_cms.form.page.meta_description',
                'required' => false,
            ])
            ->add('ogImage', TextType::class, [
                'label' => 'akawaka_cms.form.page.og_image',
                'required' => false,
            ])
            // Raw JSON field; replaced by the page builder UI in a later step.
            ->add('blocks', TextareaType::class, [
                'label' => 'akawaka_cms.form.page.blocks',
                'required' => false,
            ])
        ;

        $builder->get('blocks')->addModelTransformer(new CallbackTransformer(
            static fn (?array $blocks): string => json_encode($blocks ?? [], \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_THROW_ON_ERROR),
            static function (?string $json): array {
                $decoded = json_decode($json ?: '[]', true);

                return \is_array($decoded) ? $decoded : [];
            },
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'akawaka_cms_page_translation';
    }
}
