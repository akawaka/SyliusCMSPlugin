<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Block;

abstract class AbstractBlockType implements BlockTypeInterface
{
    public function getLabel(): string
    {
        return 'akawaka_cms.block.' . $this->getCode() . '.label';
    }

    public function getTemplate(): string
    {
        return '@AkawakaSyliusCMSPlugin/shop/blocks/' . $this->getCode() . '.html.twig';
    }

    public function getDefaults(): array
    {
        return [];
    }

    public function getViewData(array $props): array
    {
        return [];
    }

    public function acceptsChild(string $code): bool
    {
        return false;
    }

    public function isInternal(): bool
    {
        return false;
    }
}
