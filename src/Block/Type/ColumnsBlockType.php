<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Block\Type;

use AKAWAKA\SyliusCMSPlugin\Block\AbstractBlockType;
use AKAWAKA\SyliusCMSPlugin\Enum\BlockType;
use AKAWAKA\SyliusCMSPlugin\Form\Type\Block\ColumnsBlockType as ColumnsForm;

final class ColumnsBlockType extends AbstractBlockType
{
    public function getCode(): string
    {
        return BlockType::Columns->value;
    }

    public function getIcon(): string
    {
        return 'tabler:columns-3';
    }

    public function getFormType(): string
    {
        return ColumnsForm::class;
    }

    public function getDefaults(): array
    {
        return ['columns' => 2];
    }

    public function acceptsChild(string $code): bool
    {
        return BlockType::Column->value === $code;
    }
}
