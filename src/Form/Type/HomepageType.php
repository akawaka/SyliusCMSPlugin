<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Form\Type;

use Akawaka\SyliusCMSPlugin\Entity\HomepageInterface;
use Akawaka\SyliusCMSPlugin\Entity\HomepageTranslationInterface;
use Sylius\Bundle\ChannelBundle\Form\Type\ChannelChoiceType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class HomepageType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('channel', ChannelChoiceType::class, [
                'label' => 'sylius.ui.channel',
            ])
            ->add('translations', ResourceTranslationsType::class, [
                'entry_type' => HomepageTranslationType::class,
                'label' => 'sylius.form.translations',
            ])
        ;

        // Locales left untouched in the form must not create empty translations.
        $builder->addEventListener(FormEvents::SUBMIT, static function (FormEvent $event): void {
            $homepage = $event->getData();
            if (!$homepage instanceof HomepageInterface) {
                return;
            }

            /** @var HomepageTranslationInterface $translation */
            foreach ($homepage->getTranslations() as $translation) {
                if ([] === $translation->getBlocks() && '' === trim((string) $translation->getMetaTitle()) && '' === trim((string) $translation->getMetaDescription()) && '' === trim((string) $translation->getOgImage())) {
                    $homepage->removeTranslation($translation);
                }
            }
        });
    }

    public function getBlockPrefix(): string
    {
        return 'akawaka_cms_homepage';
    }
}
