<?php

declare(strict_types=1);

namespace Tests\Akawaka\SyliusCMSPlugin\Unit\Block;

use Akawaka\SyliusCMSPlugin\Block\BlockCatalog;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\IdentityTranslator;
use Tests\Akawaka\SyliusCMSPlugin\Unit\BlockFactoryTrait;

final class BlockCatalogTest extends TestCase
{
    use BlockFactoryTrait;

    public function testItDescribesBlocksForTheBuilder(): void
    {
        $catalog = (new BlockCatalog($this->createRegistry(), new IdentityTranslator()))->describe();
        $byCode = array_column($catalog, null, 'code');

        self::assertSame(['column'], $byCode['columns']['children']);
        self::assertTrue($byCode['column']['internal']);
        self::assertNotContains('column', $byCode['column']['children']);
        self::assertSame([], $byCode['image']['children']);
        self::assertSame('akawaka_cms.block.image.label', $byCode['image']['label']);
        self::assertSame(['columns' => 2], $byCode['columns']['defaults']);
    }
}
