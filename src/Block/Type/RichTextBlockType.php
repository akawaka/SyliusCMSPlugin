<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Block\Type;

use Akawaka\SyliusCMSPlugin\Block\AbstractBlockType;
use Akawaka\SyliusCMSPlugin\Enum\BlockType;
use Akawaka\SyliusCMSPlugin\Form\Type\Block\RichTextBlockType as RichTextForm;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

final class RichTextBlockType extends AbstractBlockType
{
    public function __construct(private readonly HtmlSanitizerInterface $sanitizer)
    {
    }

    public function getCode(): string
    {
        return BlockType::RichText->value;
    }

    public function getIcon(): string
    {
        return 'tabler:text-caption';
    }

    public function getFormType(): string
    {
        return RichTextForm::class;
    }

    public function getDefaults(): array
    {
        return ['html' => ''];
    }

    public function getViewData(array $props): array
    {
        $html = $props['html'] ?? '';

        return ['html' => $this->sanitizer->sanitize(\is_string($html) ? $html : '')];
    }
}
