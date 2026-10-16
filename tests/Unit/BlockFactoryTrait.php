<?php

declare(strict_types=1);

namespace Tests\Akawaka\SyliusCMSPlugin\Unit;

use Akawaka\SyliusCMSPlugin\Block\BlockRegistry;
use Akawaka\SyliusCMSPlugin\Block\Type\BannerBlockType;
use Akawaka\SyliusCMSPlugin\Block\Type\ButtonBlockType;
use Akawaka\SyliusCMSPlugin\Block\Type\ColumnBlockType;
use Akawaka\SyliusCMSPlugin\Block\Type\ColumnsBlockType;
use Akawaka\SyliusCMSPlugin\Block\Type\HtmlBlockType;
use Akawaka\SyliusCMSPlugin\Block\Type\ImageBlockType;
use Akawaka\SyliusCMSPlugin\Block\Type\RichTextBlockType;
use Akawaka\SyliusCMSPlugin\Block\Type\VideoBlockType;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

trait BlockFactoryTrait
{
    private function createRegistry(): BlockRegistry
    {
        $sanitizer = new HtmlSanitizer((new HtmlSanitizerConfig())->allowSafeElements());

        return new BlockRegistry([
            new RichTextBlockType($sanitizer),
            new ImageBlockType(),
            new BannerBlockType(),
            new ColumnsBlockType(),
            new ColumnBlockType(),
            new ButtonBlockType(),
            new VideoBlockType(),
            new HtmlBlockType(),
        ]);
    }
}
