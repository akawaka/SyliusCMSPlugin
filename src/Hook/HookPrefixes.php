<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Hook;

/** The twig hook namespaces content blocks may be attached to (e.g. "sylius_shop"). */
final readonly class HookPrefixes
{
    public const string NAME_PATTERN = '/^[a-z0-9_]+(?:[.#][a-z0-9_]+)*$/';

    /** @param list<string> $prefixes */
    public function __construct(private array $prefixes)
    {
    }

    /** @return list<string> */
    public function all(): array
    {
        return $this->prefixes;
    }

    public function allows(string $hookName): bool
    {
        if (1 !== preg_match(self::NAME_PATTERN, $hookName)) {
            return false;
        }

        foreach ($this->prefixes as $prefix) {
            if ($hookName === $prefix || str_starts_with($hookName, $prefix . '.') || str_starts_with($hookName, $prefix . '#')) {
                return true;
            }
        }

        return false;
    }
}
