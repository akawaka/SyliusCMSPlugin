<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Hook;

use Sylius\TwigHooks\Hookable\AbstractHookable;
use Sylius\TwigHooks\Hookable\Merger\HookableMerger;
use Sylius\TwigHooks\Registry\HookablesRegistry;

/**
 * Decorates the twig hooks registry to add the content blocks attached to a hook, interleaved with the configured
 * hookables by priority. It extends the registry only because the hook renderer type-hints that class; every
 * call is delegated to the decorated registry.
 */
final class CmsHookablesRegistry extends HookablesRegistry
{
    public function __construct(
        private readonly HookablesRegistry $inner,
        private readonly ContentBlockHookablesProvider $provider,
    ) {
        parent::__construct([], new HookableMerger());
    }

    public function getHookNames(): array
    {
        return $this->inner->getHookNames();
    }

    public function getEnabledFor(string|array $hooksNames): array
    {
        return $this->sort([
            ...$this->inner->getEnabledFor($hooksNames),
            ...$this->provider->forHooks(\is_string($hooksNames) ? [$hooksNames] : array_values($hooksNames)),
        ]);
    }

    public function getFor(string|array $hooksNames): array
    {
        return $this->sort([
            ...$this->inner->getFor($hooksNames),
            ...$this->provider->forHooks(\is_string($hooksNames) ? [$hooksNames] : array_values($hooksNames)),
        ]);
    }

    /**
     * The hookables of the hooks as configured by Sylius and the application, without the content blocks.
     *
     * @param string|array<string> $hooksNames
     *
     * @return array<AbstractHookable>
     */
    public function getConfiguredFor(string|array $hooksNames): array
    {
        return $this->inner->getFor($hooksNames);
    }

    /**
     * @param list<AbstractHookable> $hookables
     *
     * @return list<AbstractHookable>
     */
    private function sort(array $hookables): array
    {
        // usort is stable: hookables of the same priority keep their order, content blocks last.
        usort($hookables, static fn (AbstractHookable $a, AbstractHookable $b): int => $b->priority() <=> $a->priority());

        return $hookables;
    }
}
