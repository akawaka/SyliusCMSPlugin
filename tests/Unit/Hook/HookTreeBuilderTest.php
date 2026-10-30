<?php

declare(strict_types=1);

namespace Tests\AKAWAKA\SyliusCMSPlugin\Unit\Hook;

use AKAWAKA\SyliusCMSPlugin\Hook\HookPrefixes;
use AKAWAKA\SyliusCMSPlugin\Hook\HookTreeBuilder;
use PHPUnit\Framework\TestCase;

final class HookTreeBuilderTest extends TestCase
{
    public function testItBuildsATreeSplitOnDotsAndSections(): void
    {
        $tree = (new HookTreeBuilder(new HookPrefixes(['sylius_shop'])))->build([
            'sylius_shop.homepage.index',
            'sylius_shop.cart',
            'sylius_shop.cart.summary',
            'sylius_shop.layout#header',
            'sylius_admin.base',
        ]);

        self::assertCount(1, $tree, 'the admin hooks are filtered out');
        self::assertSame('sylius_shop', $tree[0]['label']);
        self::assertFalse($tree[0]['hook']);

        $byPath = array_column($tree[0]['children'], null, 'path');
        self::assertSame(['sylius_shop.cart', 'sylius_shop.homepage', 'sylius_shop.layout'], array_keys($byPath));

        self::assertTrue($byPath['sylius_shop.cart']['hook'], 'a node can be a hook and a parent');
        self::assertSame('sylius_shop.cart.summary', $byPath['sylius_shop.cart']['children'][0]['path']);
        self::assertFalse($byPath['sylius_shop.homepage']['hook']);
        self::assertSame('.index', $byPath['sylius_shop.homepage']['children'][0]['label']);
        self::assertSame('#header', $byPath['sylius_shop.layout']['children'][0]['label']);
        self::assertSame('sylius_shop.layout#header', $byPath['sylius_shop.layout']['children'][0]['path']);
    }

    public function testItSupportsSeveralPrefixes(): void
    {
        $tree = (new HookTreeBuilder(new HookPrefixes(['sylius_shop', 'app'])))->build(['sylius_shop.a', 'app.footer', 'other.x']);

        self::assertSame(['app', 'sylius_shop'], array_column($tree, 'label'));
    }

    public function testItReturnsNothingWithoutMatchingHook(): void
    {
        self::assertSame([], (new HookTreeBuilder(new HookPrefixes(['sylius_shop'])))->build(['sylius_admin.base']));
    }
}
