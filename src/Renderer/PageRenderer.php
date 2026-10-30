<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Renderer;

use AKAWAKA\SyliusCMSPlugin\Block\BlockRegistry;
use Twig\Environment;
use Twig\Markup;

/** Renders a JSON block tree to HTML through the Twig template of each block type. */
final class PageRenderer
{
    public function __construct(
        private readonly BlockRegistry $registry,
        private readonly Environment $twig,
    ) {
    }

    /** @param array<int, array<string, mixed>> $blocks */
    public function render(array $blocks): string
    {
        $html = '';

        foreach ($blocks as $block) {
            $html .= $this->renderBlock($block);
        }

        return $html;
    }

    /** @param array<string, mixed> $block */
    private function renderBlock(array $block): string
    {
        $code = $block['type'] ?? null;
        if (!\is_string($code) || !$this->registry->has($code)) {
            return '';
        }

        $type = $this->registry->get($code);

        $rawProps = $block['props'] ?? [];
        $props = array_replace($type->getDefaults(), \is_array($rawProps) ? $rawProps : []);

        $children = $block['children'] ?? [];
        /** @var array<int, array<string, mixed>> $children */
        $children = \is_array($children) ? $children : [];

        return $this->twig->render($type->getTemplate(), [
            'block' => ['id' => \is_string($block['id'] ?? null) ? $block['id'] : '', 'type' => $code],
            'props' => $props,
            'data' => $type->getViewData($props),
            'children' => new Markup($this->render($children), 'UTF-8'),
        ]);
    }
}
