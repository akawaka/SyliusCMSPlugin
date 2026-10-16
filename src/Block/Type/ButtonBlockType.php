<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Block\Type;

use Akawaka\SyliusCMSPlugin\Block\AbstractBlockType;
use Akawaka\SyliusCMSPlugin\Enum\Alignment;
use Akawaka\SyliusCMSPlugin\Enum\BlockType;
use Akawaka\SyliusCMSPlugin\Enum\ButtonStyle;
use Akawaka\SyliusCMSPlugin\Form\Type\Block\ButtonBlockType as ButtonForm;

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
