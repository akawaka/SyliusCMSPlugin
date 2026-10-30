<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Block\Type;

use AKAWAKA\SyliusCMSPlugin\Block\AbstractBlockType;
use AKAWAKA\SyliusCMSPlugin\Enum\BlockType;
use AKAWAKA\SyliusCMSPlugin\Form\Type\Block\VideoBlockType as VideoForm;

final class VideoBlockType extends AbstractBlockType
{
    public function getCode(): string
    {
        return BlockType::Video->value;
    }

    public function getIcon(): string
    {
        return 'tabler:video';
    }

    public function getFormType(): string
    {
        return VideoForm::class;
    }

    public function getDefaults(): array
    {
        return ['url' => '', 'autoplay' => false];
    }

    public function getViewData(array $props): array
    {
        $url = \is_string($props['url'] ?? null) ? $props['url'] : '';

        $embed = null;
        if (1 === preg_match('~(?:youtube\.com/watch\?(?:.*&)?v=|youtu\.be/|youtube\.com/embed/)([\w-]{6,})~', $url, $matches)) {
            $embed = 'https://www.youtube-nocookie.com/embed/' . $matches[1];
        } elseif (1 === preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $matches)) {
            $embed = 'https://player.vimeo.com/video/' . $matches[1];
        }

        return ['embed' => $embed, 'file' => null === $embed && '' !== $url ? $url : null];
    }
}
