<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Block\Type;

use AKAWAKA\SyliusCMSPlugin\Block\AbstractBlockType;
use AKAWAKA\SyliusCMSPlugin\Enum\BlockType;
use AKAWAKA\SyliusCMSPlugin\Form\Type\Block\HtmlBlockType as HtmlForm;

/** Renders raw, unsanitized HTML. */
final class HtmlBlockType extends AbstractBlockType
{
    public function getCode(): string
    {
        return BlockType::Html->value;
    }

    public function getIcon(): string
    {
        return 'tabler:code';
    }

    public function getFormType(): string
    {
        return HtmlForm::class;
    }

    public function getDefaults(): array
    {
        return ['code' => ''];
    }
}
