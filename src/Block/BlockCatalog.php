<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Block;

use Symfony\Contracts\Translation\TranslatorInterface;

/** Describes the registered block types for the page builder front-end. */
final readonly class BlockCatalog
{
    public function __construct(
        private BlockRegistry $registry,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @return list<array{code: string, label: string, icon: string, defaults: array<string, mixed>, internal: bool, children: list<string>}>
     */
    public function describe(): array
    {
        $types = $this->registry->all();
        $catalog = [];

        foreach ($types as $type) {
            $children = [];
            foreach ($types as $candidate) {
                if ($type->acceptsChild($candidate->getCode())) {
                    $children[] = $candidate->getCode();
                }
            }

            $catalog[] = [
                'code' => $type->getCode(),
                'label' => $this->translator->trans($type->getLabel()),
                'icon' => $type->getIcon(),
                'defaults' => $type->getDefaults(),
                'internal' => $type->isInternal(),
                'children' => $children,
            ];
        }

        return $catalog;
    }
}
