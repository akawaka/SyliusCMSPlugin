<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Enum;

enum PageTransition: string
{
    case Publish = 'publish';
    case Unpublish = 'unpublish';
}
