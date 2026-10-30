<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Block\Type;

use AKAWAKA\SyliusCMSPlugin\Block\AbstractBlockType;
use AKAWAKA\SyliusCMSPlugin\Enum\BlockType;
use AKAWAKA\SyliusCMSPlugin\Form\Type\Block\ImageBlockType as ImageForm;

final class ImageBlockType extends AbstractBlockType
{
    public function getCode(): string
    {
        return BlockType::Image->value;
    }

    public function getIcon(): string
    {
        return 'tabler:photo';
    }

    public function getFormType(): string
    {
        return ImageForm::class;
    }

    public function getDefaults(): array
    {
        return ['src' => '', 'alt' => '', 'link' => ''];
    }
}
