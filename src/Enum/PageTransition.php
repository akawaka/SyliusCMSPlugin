<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Enum;

enum PageTransition: string
{
    case Publish = 'publish';
    case Unpublish = 'unpublish';
}
