<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Block;

final class BlockRegistry
{
    /** @var array<string, BlockTypeInterface> */
    private array $types = [];

    /** @param iterable<BlockTypeInterface> $types */
    public function __construct(iterable $types)
    {
        foreach ($types as $type) {
            $this->types[$type->getCode()] = $type;
        }
    }

    public function has(string $code): bool
    {
        return isset($this->types[$code]);
    }

    public function get(string $code): BlockTypeInterface
    {
        return $this->types[$code] ?? throw new \InvalidArgumentException(sprintf('Unknown block type "%s".', $code));
    }

    /** @return array<string, BlockTypeInterface> */
    public function all(): array
    {
        return $this->types;
    }

    /** @return array<string, BlockTypeInterface> types the user can add from the palette */
    public function getInsertable(): array
    {
        return array_filter($this->types, static fn (BlockTypeInterface $type): bool => !$type->isInternal());
    }
}
