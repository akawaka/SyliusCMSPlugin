<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Block\Type;

use AKAWAKA\SyliusCMSPlugin\Block\AbstractBlockType;
use AKAWAKA\SyliusCMSPlugin\Enum\Alignment;
use AKAWAKA\SyliusCMSPlugin\Enum\BlockType;
use AKAWAKA\SyliusCMSPlugin\Form\Type\Block\BannerBlockType as BannerForm;

final class BannerBlockType extends AbstractBlockType
{
    public function getCode(): string
    {
        return BlockType::Banner->value;
    }

    public function getIcon(): string
    {
        return 'tabler:layout-navbar';
    }

    public function getFormType(): string
    {
        return BannerForm::class;
    }

    public function getDefaults(): array
    {
        return [
            'title' => '',
            'subtitle' => '',
            'image' => '',
            'buttonLabel' => '',
            'buttonUrl' => '',
            'alignment' => Alignment::Center->value,
        ];
    }
}
