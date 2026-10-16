<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Enum;

enum PageStatus: string
{
    case Draft = 'draft';
    case Published = 'published';

    public function label(): string
    {
        return 'akawaka_cms.ui.status.' . $this->value;
    }
}
