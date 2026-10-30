<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Block\Type;

use AKAWAKA\SyliusCMSPlugin\Block\AbstractBlockType;
use AKAWAKA\SyliusCMSPlugin\Enum\BlockType;
use AKAWAKA\SyliusCMSPlugin\Form\Type\Block\ColumnBlockType as ColumnForm;

final class ColumnBlockType extends AbstractBlockType
{
    public function getCode(): string
    {
        return BlockType::Column->value;
    }

    public function getIcon(): string
    {
        return 'tabler:layout-sidebar';
    }

    public function getFormType(): string
    {
        return ColumnForm::class;
    }

    public function acceptsChild(string $code): bool
    {
        return BlockType::Column->value !== $code;
    }

    public function isInternal(): bool
    {
        return true;
    }
}
