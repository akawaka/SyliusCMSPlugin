<?php

declare(strict_types=1);

namespace Tests\AKAWAKA\SyliusCMSPlugin\Unit\Block;

use AKAWAKA\SyliusCMSPlugin\Enum\BlockType;
use PHPUnit\Framework\TestCase;
use Tests\AKAWAKA\SyliusCMSPlugin\Unit\BlockFactoryTrait;

final class BlockRegistryTest extends TestCase
{
    use BlockFactoryTrait;

    public function testItResolvesTypesByCode(): void
    {
        $registry = $this->createRegistry();

        self::assertTrue($registry->has(BlockType::Image->value));
        self::assertSame(BlockType::Image->value, $registry->get(BlockType::Image->value)->getCode());
        self::assertFalse($registry->has('nope'));
    }

    public function testItThrowsOnUnknownType(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->createRegistry()->get('nope');
    }

    public function testInternalTypesAreNotInsertable(): void
    {
        $insertable = $this->createRegistry()->getInsertable();

        self::assertArrayNotHasKey(BlockType::Column->value, $insertable);
        self::assertArrayHasKey(BlockType::Columns->value, $insertable);
    }
}
