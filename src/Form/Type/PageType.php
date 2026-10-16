<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Form\Type;

use Akawaka\SyliusCMSPlugin\Entity\PageInterface;
use Akawaka\SyliusCMSPlugin\Entity\PageTranslationInterface;
use Sylius\Bundle\ChannelBundle\Form\Type\ChannelChoiceType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class PageType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, ['label' => 'sylius.ui.code'])
            ->add('channels', ChannelChoiceType::class, [
                'label' => 'sylius.ui.channels',
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('translations', ResourceTranslationsType::class, [
                'entry_type' => PageTranslationType::class,
                'label' => 'sylius.form.translations',
            ])
        ;

        // Locales left untouched in the form must not create empty translations.
        $builder->addEventListener(FormEvents::SUBMIT, static function (FormEvent $event): void {
            $page = $event->getData();
            if (!$page instanceof PageInterface) {
                return;
            }

            /** @var PageTranslationInterface $translation */
            foreach ($page->getTranslations() as $translation) {
                if ('' === trim((string) $translation->getTitle()) && '' === trim((string) $translation->getSlug()) && [] === $translation->getBlocks()) {
                    $page->removeTranslation($translation);
                }
            }
        });
    }

    public function getBlockPrefix(): string
    {
        return 'akawaka_cms_page';
    }
}
