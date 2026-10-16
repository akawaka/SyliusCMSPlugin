<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Enum;

enum ButtonStyle: string
{
    case Primary = 'primary';
    case Secondary = 'secondary';
    case Outline = 'outline-primary';

    public function label(): string
    {
        return 'akawaka_cms.ui.button_style.' . $this->value;
    }
}
