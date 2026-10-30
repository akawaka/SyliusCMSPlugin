<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Block\Type;

use AKAWAKA\SyliusCMSPlugin\Block\AbstractBlockType;
use AKAWAKA\SyliusCMSPlugin\Enum\Alignment;
use AKAWAKA\SyliusCMSPlugin\Enum\BlockType;
use AKAWAKA\SyliusCMSPlugin\Enum\ButtonStyle;
use AKAWAKA\SyliusCMSPlugin\Form\Type\Block\ButtonBlockType as ButtonForm;

final class ButtonBlockType extends AbstractBlockType
{
    public function getCode(): string
    {
        return BlockType::Button->value;
    }

    public function getIcon(): string
    {
        return 'tabler:click';
    }

    public function getFormType(): string
    {
        return ButtonForm::class;
    }

    public function getDefaults(): array
    {
        return [
            'label' => '',
            'url' => '',
            'style' => ButtonStyle::Primary->value,
            'alignment' => Alignment::Left->value,
        ];
    }
}
