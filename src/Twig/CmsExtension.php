<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Twig;

use Akawaka\SyliusCMSPlugin\Renderer\PageRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class CmsExtension extends AbstractExtension
{
    public function __construct(private readonly PageRenderer $renderer)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('cms_render', $this->renderer->render(...), ['is_safe' => ['html']]),
        ];
    }
}
