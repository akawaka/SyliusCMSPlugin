<?php

declare(strict_types=1);

namespace Tests\AKAWAKA\SyliusCMSPlugin\Unit\Hook;

use AKAWAKA\SyliusCMSPlugin\Entity\ContentBlock;
use AKAWAKA\SyliusCMSPlugin\Entity\ContentBlockTranslation;
use AKAWAKA\SyliusCMSPlugin\Enum\PageStatus;
use AKAWAKA\SyliusCMSPlugin\Hook\ContentBlockHookablesProvider;
use AKAWAKA\SyliusCMSPlugin\Hook\HookPrefixes;
use AKAWAKA\SyliusCMSPlugin\Repository\ContentBlockRepository;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;

final class ContentBlockHookablesProviderTest extends TestCase
{
    public function testItBuildsOneHookablePerPlacementWithItsPriority(): void
    {
        $provider = $this->provider([$this->block('promo', [['hook' => 'sylius_shop.cart', 'priority' => 40], ['hook' => 'sylius_shop.home', 'priority' => -5]], 'en_US', [['id' => 'a']])]);

        $hookables = $provider->forHooks(['sylius_shop.cart']);

        self::assertCount(1, $hookables);
        self::assertSame('akawaka_cms_block_promo', $hookables[0]->name);
        self::assertSame(40, $hookables[0]->priority());
        self::assertSame('promo', $hookables[0]->context['cms_block_code']);
        self::assertSame([['id' => 'a']], $hookables[0]->context['cms_blocks']);
        self::assertSame(-5, $provider->forHooks(['sylius_shop.home'])[0]->priority());
    }

    public function testBlocksWithoutContentInTheLocaleAreSkipped(): void
    {
        $provider = $this->provider([
            $this->block('fr', [['hook' => 'sylius_shop.cart', 'priority' => 0]], 'fr_FR', [['id' => 'a']]),
            $this->block('empty', [['hook' => 'sylius_shop.cart', 'priority' => 0]], 'en_US', []),
        ]);

        self::assertSame([], $provider->forHooks(['sylius_shop.cart']));
    }

    public function testHooksOutsideTheAllowedPrefixesNeverQueryTheDatabase(): void
    {
        $repository = $this->createMock(ContentBlockRepository::class);
        $repository->expects(self::never())->method('findPublishedForChannel');

        self::assertSame([], $this->provider([], $repository)->forHooks(['sylius_admin.base']));
    }

    public function testTheBlocksAreLoadedOncePerRequestAndAgainAfterAReset(): void
    {
        $repository = $this->createMock(ContentBlockRepository::class);
        $repository->expects(self::exactly(2))->method('findPublishedForChannel')->willReturn([]);
        $provider = $this->provider([], $repository);

        $provider->forHooks(['sylius_shop.cart']);
        $provider->forHooks(['sylius_shop.home']);
        $provider->reset();
        $provider->forHooks(['sylius_shop.cart']);
    }

    public function testItDoesNothingWithoutAChannel(): void
    {
        $channelContext = $this->createMock(ChannelContextInterface::class);
        $channelContext->method('getChannel')->willThrowException(new ChannelNotFoundException());
        $repository = $this->createMock(ContentBlockRepository::class);
        $repository->expects(self::never())->method('findPublishedForChannel');

        $provider = new ContentBlockHookablesProvider($repository, $channelContext, $this->localeContext(), new HookPrefixes(['sylius_shop']));

        self::assertSame([], $provider->forHooks(['sylius_shop.cart']));
    }

    /** @param list<ContentBlock> $blocks */
    private function provider(array $blocks, ?ContentBlockRepository $repository = null): ContentBlockHookablesProvider
    {
        if (null === $repository) {
            $repository = $this->createMock(ContentBlockRepository::class);
            $repository->method('findPublishedForChannel')->willReturn($blocks);
        }

        $channelContext = $this->createMock(ChannelContextInterface::class);
        $channelContext->method('getChannel')->willReturn($this->createMock(ChannelInterface::class));

        return new ContentBlockHookablesProvider($repository, $channelContext, $this->localeContext(), new HookPrefixes(['sylius_shop']));
    }

    private function localeContext(): LocaleContextInterface
    {
        $context = $this->createMock(LocaleContextInterface::class);
        $context->method('getLocaleCode')->willReturn('en_US');

        return $context;
    }

    /**
     * @param list<array{hook: string, priority: int}> $placements
     * @param array<int, array<string, mixed>> $blocks
     */
    private function block(string $code, array $placements, string $locale, array $blocks): ContentBlock
    {
        $block = new ContentBlock();
        $block->setCode($code);
        $block->setStatus(PageStatus::Published);
        $block->setPlacements($placements);

        $translation = new ContentBlockTranslation();
        $translation->setLocale($locale);
        $translation->setBlocks($blocks);
        $block->addTranslation($translation);

        return $block;
    }
}
