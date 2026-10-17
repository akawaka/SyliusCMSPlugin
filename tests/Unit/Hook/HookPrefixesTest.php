<?php

declare(strict_types=1);

namespace Tests\Akawaka\SyliusCMSPlugin\Unit\Hook;

use Akawaka\SyliusCMSPlugin\Hook\HookPrefixes;
use PHPUnit\Framework\TestCase;

final class HookPrefixesTest extends TestCase
{
    /** @dataProvider names */
    public function testItAllowsOnlyWellFormedNamesInTheConfiguredNamespaces(string $name, bool $allowed): void
    {
        self::assertSame($allowed, (new HookPrefixes(['sylius_shop', 'app']))->allows($name));
    }

    /** @return iterable<string, array{string, bool}> */
    public static function names(): iterable
    {
        yield 'prefix itself' => ['sylius_shop', true];
        yield 'child' => ['sylius_shop.cart.summary', true];
        yield 'section' => ['sylius_shop.layout#header', true];
        yield 'other prefix' => ['app.footer', true];
        yield 'admin' => ['sylius_admin.base', false];
        yield 'lookalike prefix' => ['sylius_shopping.cart', false];
        yield 'uppercase' => ['sylius_shop.Cart', false];
        yield 'path traversal' => ['sylius_shop.../x', false];
        yield 'trailing dot' => ['sylius_shop.', false];
        yield 'whitespace' => ['sylius_shop.cart summary', false];
        yield 'empty' => ['', false];
    }
}
