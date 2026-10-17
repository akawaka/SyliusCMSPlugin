<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Entity;

use Akawaka\SyliusCMSPlugin\Enum\PageStatus;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;

/** The storefront homepage of a channel, built with the page builder. */
interface HomepageInterface extends ResourceInterface, TranslatableInterface, TimestampableInterface
{
    public function getChannel(): ?ChannelInterface;

    public function setChannel(?ChannelInterface $channel): void;

    public function getStatus(): PageStatus;

    public function setStatus(PageStatus $status): void;

    public function isPublished(): bool;

    public function getHomepageTranslation(?string $locale = null): HomepageTranslationInterface;
}
