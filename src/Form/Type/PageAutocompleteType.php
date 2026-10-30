<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Form\Type;

use AKAWAKA\SyliusCMSPlugin\Entity\PageInterface;
use AKAWAKA\SyliusCMSPlugin\Enum\PageStatus;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\AdminBundle\Form\Type\TranslatableAutocompleteType;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;

/**
 * Select with autocompletion of CMS pages, searching on the title and slug (current locale) and on the code.
 *
 * The field value is the page entity. Customize it through `extra_options`, the only options sent back to the
 * autocomplete endpoint:
 *  - `only_published` (bool, default true): hide the draft pages;
 *  - `channel_code` (string|null): only pages available in this channel.
 */
#[AsEntityAutocompleteField(
    alias: 'akawaka_cms_page',
    route: 'sylius_admin_entity_autocomplete',
)]
final class PageAutocompleteType extends AbstractType
{
    public function __construct(
        private readonly string $pageClass,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => $this->pageClass,
            'entity_fields' => ['code'],
            'translation_fields' => ['title', 'slug'],
        ]);

        $resolver->setDefault('choice_label', static function (Options $options): mixed {
            /** @var array<string, mixed> $extraOptions */
            $extraOptions = $options['extra_options'];

            return $extraOptions['choice_label'] ?? self::label(...);
        });

        $resolver->setAllowedTypes('extra_options', 'array');

        $resolver->addNormalizer('filter_query', static function (Options $options, ?callable $filterQuery): callable {
            /** @var array<string, mixed> $extraOptions */
            $extraOptions = $options['extra_options'];
            $onlyPublished = (bool) ($extraOptions['only_published'] ?? true);
            $channelCode = $extraOptions['channel_code'] ?? null;

            return static function (QueryBuilder $queryBuilder, string $query, EntityRepository $repository) use ($filterQuery, $onlyPublished, $channelCode): void {
                if (null !== $filterQuery) {
                    $filterQuery($queryBuilder, $query, $repository);
                }

                if ($onlyPublished) {
                    $queryBuilder
                        ->andWhere('entity.status = :akawaka_cms_status')
                        ->setParameter('akawaka_cms_status', PageStatus::Published)
                    ;
                }

                if (\is_string($channelCode)) {
                    $queryBuilder
                        ->innerJoin('entity.channels', 'akawaka_cms_channel', 'WITH', 'akawaka_cms_channel.code = :akawaka_cms_channel_code')
                        ->setParameter('akawaka_cms_channel_code', $channelCode)
                    ;
                }
            };
        });
    }

    public function getBlockPrefix(): string
    {
        return 'akawaka_cms_page_autocomplete';
    }

    public function getParent(): string
    {
        return TranslatableAutocompleteType::class;
    }

    private static function label(PageInterface $page): string
    {
        $title = $page->getTitle();
        $slug = $page->getSlug();

        if (null === $title || '' === $title) {
            return (string) $page->getCode();
        }

        return null === $slug || '' === $slug ? $title : sprintf('%s (/%s)', $title, $slug);
    }
}
