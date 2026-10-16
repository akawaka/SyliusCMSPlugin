<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Block\Type;

use Akawaka\SyliusCMSPlugin\Block\AbstractBlockType;
use Akawaka\SyliusCMSPlugin\Enum\BlockType;
use Akawaka\SyliusCMSPlugin\Form\Type\Block\HtmlBlockType as HtmlForm;

/** Renders raw, unsanitized HTML: only trusted administrators should be allowed to use it. */
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
