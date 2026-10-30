<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Twig;

use AKAWAKA\SyliusCMSPlugin\Hook\CmsHookablesRegistry;
use AKAWAKA\SyliusCMSPlugin\Hook\HookTreeBuilder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/** Exposes the tree of the twig hooks content blocks can be attached to (shown by the hook picker). */
final class HookTreeExtension extends AbstractExtension
{
    public function __construct(
        private readonly HookTreeBuilder $builder,
        private readonly CmsHookablesRegistry $registry,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('akawaka_cms_hook_tree', fn (): array => $this->builder->build($this->registry->getHookNames())),
        ];
    }
}
