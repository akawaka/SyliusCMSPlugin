<?php

declare(strict_types=1);

namespace Tests\AKAWAKA\SyliusCMSPlugin\Unit;

use AKAWAKA\SyliusCMSPlugin\Block\BlockRegistry;
use AKAWAKA\SyliusCMSPlugin\Block\Type\BannerBlockType;
use AKAWAKA\SyliusCMSPlugin\Block\Type\ButtonBlockType;
use AKAWAKA\SyliusCMSPlugin\Block\Type\ColumnBlockType;
use AKAWAKA\SyliusCMSPlugin\Block\Type\ColumnsBlockType;
use AKAWAKA\SyliusCMSPlugin\Block\Type\HtmlBlockType;
use AKAWAKA\SyliusCMSPlugin\Block\Type\ImageBlockType;
use AKAWAKA\SyliusCMSPlugin\Block\Type\RichTextBlockType;
use AKAWAKA\SyliusCMSPlugin\Block\Type\VideoBlockType;
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
