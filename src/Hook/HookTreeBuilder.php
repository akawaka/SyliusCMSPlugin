<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Hook;

/**
 * Turns flat twig hook names ("sylius_shop.product.show.content", "sylius_admin.base#javascripts") into the tree
 * shown by the hook picker. A node may be a hook itself and the parent of other hooks at the same time.
 */
final readonly class HookTreeBuilder
{
    public function __construct(private HookPrefixes $prefixes)
    {
    }

    /**
     * @param iterable<string> $hookNames
     *
     * @return list<array{label: string, path: string, hook: bool, children: list<array<string, mixed>>}>
     */
    public function build(iterable $hookNames): array
    {
        /** @var array<string, true> $hooks */
        $hooks = [];
        /** @var array<string, array<string, string>> $children parent path => child path => label */
        $children = [];

        foreach ($hookNames as $hookName) {
            if (!$this->prefixes->allows($hookName)) {
                continue;
            }

            $hooks[$hookName] = true;

            // "a.b#c" => ["a", ".b", "#c"]: each segment keeps the separator that leads to it.
            $parent = '';
            $path = '';
            foreach (preg_split('/(?=[.#])/', $hookName) ?: [] as $segment) {
                $path .= $segment;
                $children[$parent][$path] = $segment;
                $parent = $path;
            }
        }

        return $this->nodes('', $children, $hooks);
    }

    /**
     * @param array<string, array<string, string>> $children
     * @param array<string, true> $hooks
     *
     * @return list<array{label: string, path: string, hook: bool, children: list<array<string, mixed>>}>
     */
    private function nodes(string $parent, array $children, array $hooks): array
    {
        $level = $children[$parent] ?? [];
        ksort($level);

        $nodes = [];
        foreach ($level as $path => $label) {
            $nodes[] = [
                'label' => $label,
                'path' => (string) $path,
                'hook' => isset($hooks[$path]),
                'children' => $this->nodes((string) $path, $children, $hooks),
            ];
        }

        return $nodes;
    }
}
