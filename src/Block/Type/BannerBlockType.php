<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Block\Type;

use Akawaka\SyliusCMSPlugin\Block\AbstractBlockType;
use Akawaka\SyliusCMSPlugin\Enum\Alignment;
use Akawaka\SyliusCMSPlugin\Enum\BlockType;
use Akawaka\SyliusCMSPlugin\Form\Type\Block\BannerBlockType as BannerForm;

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
