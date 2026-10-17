<?php

declare(strict_types=1);

namespace Tests\Akawaka\SyliusCMSPlugin\Functional;

use Akawaka\SyliusCMSPlugin\Enum\PageStatus;

final class ShopPageTest extends FunctionalTestCase
{
    public function testAPublishedPageIsRenderedWithItsBlocksAndSeoTags(): void
    {
        $this->createPage('about', 'functional-about', blocks: [
            ['id' => 'b1', 'type' => 'rich_text', 'props' => ['html' => '<p>Hello <script>alert(1)</script>world</p>']],
            ['id' => 'b2', 'type' => 'button', 'props' => ['label' => 'Shop now', 'url' => '/en_US/', 'style' => 'primary']],
        ]);

        $crawler = $this->client->request('GET', '/en_US/pages/functional-about');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Hello', $crawler->filter('[data-cms-block="b1"]')->text());
        self::assertStringNotContainsString('<script>alert', (string) $this->client->getResponse()->getContent(), 'rich text is sanitized');
        self::assertSelectorTextContains('[data-cms-block="b2"]', 'Shop now');
        self::assertSame('http://localhost/en_US/pages/functional-about', $crawler->filter('link[rel="canonical"]')->attr('href'));
        self::assertSame('Description about', $crawler->filter('meta[name="description"]')->attr('content'));
        self::assertCount(1, $crawler->filter('link[rel="alternate"][hreflang="en-US"]'));
        self::assertSame('Title about', $crawler->filter('meta[property="og:title"]')->attr('content'));
    }

    public function testADraftPageIsNotFound(): void
    {
        $this->createPage('draft', 'functional-draft', PageStatus::Draft);

        $this->client->request('GET', '/en_US/pages/functional-draft');

        self::assertResponseStatusCodeSame(404);
    }

    public function testAnUnknownPageIsNotFound(): void
    {
        $this->client->request('GET', '/en_US/pages/functional-does-not-exist');

        self::assertResponseStatusCodeSame(404);
    }

    public function testAPageIsNotServedInALocaleItIsNotTranslatedIn(): void
    {
        $this->createPage('locale', 'functional-locale');

        // fr_FR is not a locale of the channel: the storefront redirects instead of serving the page.
        $this->client->request('GET', '/fr_FR/pages/functional-locale');

        self::assertFalse($this->client->getResponse()->isSuccessful());
    }

    public function testNestedSlugsAreSupported(): void
    {
        $this->createPage('nested', 'functional/legal/terms');

        $this->client->request('GET', '/en_US/pages/functional/legal/terms');

        self::assertResponseIsSuccessful();
    }

    public function testAnUnknownPreviewTokenIsNotFound(): void
    {
        $this->client->request('GET', '/en_US/cms-preview/' . str_repeat('a', 32));

        self::assertResponseStatusCodeSame(404);
    }
}
