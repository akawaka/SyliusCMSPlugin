<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Form\Type;

use Akawaka\SyliusCMSPlugin\Entity\ContentBlockInterface;
use Akawaka\SyliusCMSPlugin\Entity\ContentBlockTranslationInterface;
use Akawaka\SyliusCMSPlugin\Form\DataTransformer\PlacementsJsonTransformer;
use Sylius\Bundle\ChannelBundle\Form\Type\ChannelChoiceType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class ContentBlockType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'akawaka_cms.form.content_block.name'])
            ->add('code', TextType::class, ['label' => 'sylius.ui.code'])
            ->add('channels', ChannelChoiceType::class, [
                'label' => 'sylius.ui.channels',
                'help' => 'akawaka_cms.form.content_block.channels_help',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
            // Raw JSON written by the hook picker.
            ->add('placements', HiddenType::class, ['required' => false])
            ->add('translations', ResourceTranslationsType::class, [
                'entry_type' => ContentBlockTranslationType::class,
                'label' => 'sylius.form.translations',
            ])
        ;

        $builder->get('placements')->addModelTransformer(new PlacementsJsonTransformer());

        // Locales left untouched in the form must not create empty translations.
        $builder->addEventListener(FormEvents::SUBMIT, static function (FormEvent $event): void {
            $block = $event->getData();
            if (!$block instanceof ContentBlockInterface) {
                return;
            }

            /** @var ContentBlockTranslationInterface $translation */
            foreach ($block->getTranslations() as $translation) {
                if ([] === $translation->getBlocks()) {
                    $block->removeTranslation($translation);
                }
            }
        });
    }

    public function getBlockPrefix(): string
    {
        return 'akawaka_cms_content_block';
    }
}
