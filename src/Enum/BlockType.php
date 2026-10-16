<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Enum;

enum BlockType: string
{
    case RichText = 'rich_text';
    case Image = 'image';
    case Banner = 'banner';
    case Columns = 'columns';
    case Column = 'column';
    case ProductGrid = 'product_grid';
    case Button = 'button';
    case Video = 'video';
    case Html = 'html';
}
