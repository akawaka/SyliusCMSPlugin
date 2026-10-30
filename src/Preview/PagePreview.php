<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Preview;

use AKAWAKA\SyliusCMSPlugin\Entity\PageTranslationInterface;

/** Unsaved content of one page translation, rendered by the storefront preview. */
final readonly class PagePreview
{
    /** @param array<int, array<string, mixed>> $blocks */
    public function __construct(
        public string $locale,
        public ?string $title,
        public ?string $slug,
        public ?string $metaTitle,
        public ?string $metaDescription,
        public ?string $ogImage,
        public array $blocks,
    ) {
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        $string = static fn (mixed $value): ?string => \is_string($value) && '' !== $value ? $value : null;

        $blocks = $data['blocks'] ?? [];
        /** @var array<int, array<string, mixed>> $blocks */
        $blocks = \is_array($blocks) ? array_values(array_filter($blocks, \is_array(...))) : [];

        return new self(
            \is_string($data['locale'] ?? null) ? $data['locale'] : 'en_US',
            $string($data['title'] ?? null),
            $string($data['slug'] ?? null),
            $string($data['metaTitle'] ?? null),
            $string($data['metaDescription'] ?? null),
            $string($data['ogImage'] ?? null),
            $blocks,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /** Fills a blank translation, to be created by the caller from the page translation resource factory. */
    public function applyTo(PageTranslationInterface $translation): PageTranslationInterface
    {
        $translation->setLocale($this->locale);
        $translation->setTitle($this->title);
        $translation->setSlug($this->slug);
        $translation->setMetaTitle($this->metaTitle);
        $translation->setMetaDescription($this->metaDescription);
        $translation->setOgImage($this->ogImage);
        $translation->setBlocks($this->blocks);

        return $translation;
    }
}
