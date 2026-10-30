<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Entity;

use AKAWAKA\SyliusCMSPlugin\Enum\PageStatus;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;

/** Reusable content, built with the page builder and attached to twig hooks of the storefront. */
interface ContentBlockInterface extends ResourceInterface, TranslatableInterface, TimestampableInterface
{
    public function getCode(): ?string;

    public function setCode(?string $code): void;

    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getStatus(): PageStatus;

    public function setStatus(PageStatus $status): void;

    public function isPublished(): bool;

    /**
     * Channels the block is displayed on; none means every channel.
     *
     * @return Collection<int, ChannelInterface>
     */
    public function getChannels(): Collection;

    public function addChannel(ChannelInterface $channel): void;

    public function removeChannel(ChannelInterface $channel): void;

    public function appliesToChannel(ChannelInterface $channel): bool;

    /** @return list<array{hook: string, priority: int}> */
    public function getPlacements(): array;

    /** @param list<array{hook: string, priority: int}> $placements */
    public function setPlacements(array $placements): void;

    public function getContentBlockTranslation(?string $locale = null): ContentBlockTranslationInterface;
}
