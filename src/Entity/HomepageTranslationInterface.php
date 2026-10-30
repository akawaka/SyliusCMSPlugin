<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslationInterface;

interface HomepageTranslationInterface extends ResourceInterface, TranslationInterface
{
    /** @return array<int, array<string, mixed>> */
    public function getBlocks(): array;

    /** @param array<int, array<string, mixed>> $blocks */
    public function setBlocks(array $blocks): void;

    public function getMetaTitle(): ?string;

    public function setMetaTitle(?string $metaTitle): void;

    public function getMetaDescription(): ?string;

    public function setMetaDescription(?string $metaDescription): void;

    public function getOgImage(): ?string;

    public function setOgImage(?string $ogImage): void;
}
