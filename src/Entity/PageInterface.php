<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Entity;

use AKAWAKA\SyliusCMSPlugin\Enum\PageStatus;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;

interface PageInterface extends ResourceInterface, TranslatableInterface, TimestampableInterface
{
    public function getCode(): ?string;

    public function setCode(?string $code): void;

    public function getStatus(): PageStatus;

    public function setStatus(PageStatus $status): void;

    public function isPublished(): bool;

    /** @return Collection<int, ChannelInterface> */
    public function getChannels(): Collection;

    public function addChannel(ChannelInterface $channel): void;

    public function removeChannel(ChannelInterface $channel): void;

    public function getTitle(): ?string;

    public function getSlug(): ?string;

    public function getPageTranslation(?string $locale = null): PageTranslationInterface;
}
