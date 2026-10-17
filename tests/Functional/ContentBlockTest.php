<?php

declare(strict_types=1);

namespace Tests\Akawaka\SyliusCMSPlugin\Functional;

use Akawaka\SyliusCMSPlugin\Entity\ContentBlock;
use Akawaka\SyliusCMSPlugin\Enum\PageStatus;

final class ContentBlockTest extends FunctionalTestCase
{
    private const HOOK = 'sylius_shop.homepage.index';

    public function testABlockIsRenderedInsideItsHookByPriority(): void
    {
        // The homepage hook renders banner (300), latest_deals (200), new_collection (100) and latest_products (0).
        $this->createContentBlock('top', [['hook' => self::HOOK, 'priority' => 400]]);
        $this->createContentBlock('middle', [['hook' => self::HOOK, 'priority' => 250]]);
        $this->createContentBlock('bottom', [['hook' => self::HOOK, 'priority' => -50]]);

        $this->client->request('GET', '/en_US/');
        $html = (string) $this->client->getResponse()->getContent();

        self::assertResponseIsSuccessful();
        $positions = [
            'top' => $this->position($html, 'data-cms-content-block="functional_top"'),
            'banner' => $this->position($html, 'role="banner"'),
            'middle' => $this->position($html, 'data-cms-content-block="functional_middle"'),
            'bottom' => $this->position($html, 'data-cms-content-block="functional_bottom"'),
            'footer' => $this->position($html, '<footer'),
        ];

        self::assertSame(array_keys($positions), array_keys($this->sorted($positions)), 'blocks are interleaved with the existing hookables by priority');
        self::assertStringContainsString('Content of middle', $html);
    }

    public function testABlockAttachedToSeveralHooksIsRenderedInEach(): void
    {
        $this->createContentBlock('multi', [['hook' => self::HOOK, 'priority' => 0], ['hook' => 'sylius_shop.cart.summary', 'priority' => 0]]);

        $this->client->request('GET', '/en_US/');

        self::assertSame(1, substr_count((string) $this->client->getResponse()->getContent(), 'data-cms-content-block="functional_multi"'));
    }

    public function testADraftBlockIsNotRendered(): void
    {
        $this->createContentBlock('draft', [['hook' => self::HOOK, 'priority' => 0]], PageStatus::Draft);

        $this->client->request('GET', '/en_US/');

        self::assertSelectorNotExists('[data-cms-content-block="functional_draft"]');
    }

    public function testABlockWithoutContentInTheLocaleIsNotRendered(): void
    {
        $this->createContentBlock('fr_only', [['hook' => self::HOOK, 'priority' => 0]], locale: 'fr_FR');

        $this->client->request('GET', '/en_US/');

        self::assertSelectorNotExists('[data-cms-content-block="functional_fr_only"]');
    }

    public function testABlockRestrictedToAnotherChannelIsNotRendered(): void
    {
        $this->createContentBlock('other', [['hook' => self::HOOK, 'priority' => 0]], channels: [$this->otherChannel()]);
        $this->createContentBlock('mine', [['hook' => self::HOOK, 'priority' => 0]], channels: [$this->channel()]);

        $this->client->request('GET', '/en_US/');

        self::assertSelectorNotExists('[data-cms-content-block="functional_other"]');
        self::assertSelectorExists('[data-cms-content-block="functional_mine"]');
    }

    public function testABlockOnAnotherHookDoesNotAppearElsewhere(): void
    {
        $this->createContentBlock('cart', [['hook' => 'sylius_shop.cart.summary', 'priority' => 0]]);

        $this->client->request('GET', '/en_US/');

        self::assertSelectorNotExists('[data-cms-content-block="functional_cart"]');
    }

    public function testTheHookTreeIsLimitedToTheAllowedPrefixes(): void
    {
        $this->logInAsAdministrator();
        $crawler = $this->client->request('GET', '/admin/cms/content-blocks/new');

        self::assertResponseIsSuccessful();
        $tree = (string) $crawler->filter('[data-akawaka-cms-hook-picker-tree-value]')->attr('data-akawaka-cms-hook-picker-tree-value');
        self::assertStringContainsString('sylius_shop', $tree);
        self::assertStringNotContainsString('sylius_admin', $tree);
    }

    public function testTheHookInfoListsTheHookablesOfAHook(): void
    {
        $this->logInAsAdministrator();

        $this->client->request('GET', '/admin/cms/hooks/' . self::HOOK);

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertIsArray($data);
        $priorities = array_column($data['hookables'], 'priority', 'name');
        self::assertSame(300, $priorities['banner']);
        self::assertSame(200, $priorities['latest_deals']);
    }

    public function testTheHookInfoRefusesHooksOutsideTheAllowedPrefixes(): void
    {
        $this->logInAsAdministrator();

        $this->client->request('GET', '/admin/cms/hooks/sylius_admin.base');

        self::assertResponseStatusCodeSame(404);
    }

    public function testTheAdminCreatesAPublishesAndDisplaysABlock(): void
    {
        $this->logInAsAdministrator();
        $crawler = $this->client->request('GET', '/admin/cms/content-blocks/new');
        $form = $crawler->filter('form[name="akawaka_cms_content_block"]')->form();
        $form['akawaka_cms_content_block[name]'] = 'Promo';
        $form['akawaka_cms_content_block[code]'] = 'functional_promo';
        $form['akawaka_cms_content_block[placements]'] = json_encode([['hook' => self::HOOK, 'priority' => 150]], \JSON_THROW_ON_ERROR);
        $form['akawaka_cms_content_block[translations][en_US][blocks]'] = json_encode([['id' => 'p', 'type' => 'rich_text', 'props' => ['html' => '<p>Promo content</p>']]], \JSON_THROW_ON_ERROR);
        $this->client->submit($form);
        self::assertResponseRedirects();

        $block = $this->entityManager()->getRepository(ContentBlock::class)->findOneBy(['code' => 'functional_promo']);
        self::assertInstanceOf(ContentBlock::class, $block);
        self::assertSame([['hook' => self::HOOK, 'priority' => 150]], $block->getPlacements());

        $crawler = $this->client->request('GET', '/admin/cms/content-blocks/');
        $publish = $crawler->filter('form[action$="/' . $block->getId() . '/publish"]');
        self::assertCount(1, $publish);
        $this->client->submit($publish->form());

        $this->client->request('GET', '/en_US/');
        self::assertSelectorTextContains('[data-cms-content-block="functional_promo"]', 'Promo content');
    }

    /** @dataProvider invalidPlacements */
    public function testInvalidPlacementsAreRefused(string $placements): void
    {
        $this->logInAsAdministrator();
        $crawler = $this->client->request('GET', '/admin/cms/content-blocks/new');
        $form = $crawler->filter('form[name="akawaka_cms_content_block"]')->form();
        $form['akawaka_cms_content_block[name]'] = 'Invalid';
        $form['akawaka_cms_content_block[code]'] = 'functional_invalid';
        $form['akawaka_cms_content_block[placements]'] = $placements;
        $this->client->submit($form);

        self::assertFalse($this->client->getResponse()->isRedirect());
        self::assertNull($this->entityManager()->getRepository(ContentBlock::class)->findOneBy(['code' => 'functional_invalid']));
    }

    /** @return iterable<string, array{string}> */
    public static function invalidPlacements(): iterable
    {
        yield 'admin hook' => ['[{"hook":"sylius_admin.base","priority":0}]'];
        yield 'malformed name' => ['[{"hook":"sylius_shop.../x","priority":0}]'];
        yield 'priority out of range' => ['[{"hook":"sylius_shop.cart.summary","priority":5000}]'];
        yield 'duplicate hook' => ['[{"hook":"sylius_shop.cart.summary","priority":0},{"hook":"sylius_shop.cart.summary","priority":1}]'];
        yield 'not a list' => ['{"hook":"sylius_shop.cart.summary","priority":0}'];
    }

    private function position(string $html, string $needle): int
    {
        $position = strpos($html, $needle);
        self::assertNotFalse($position, sprintf('"%s" is rendered', $needle));

        return $position;
    }

    /**
     * @param array<string, int> $positions
     *
     * @return array<string, int>
     */
    private function sorted(array $positions): array
    {
        asort($positions);

        return $positions;
    }
}
