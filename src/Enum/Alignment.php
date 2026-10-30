<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Enum;

enum Alignment: string
{
    case Left = 'left';
    case Center = 'center';
    case Right = 'right';

    public function label(): string
    {
        return 'akawaka_cms.ui.alignment.' . $this->value;
    }
}
