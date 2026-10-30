<?php

declare(strict_types=1);

namespace Tests\AKAWAKA\SyliusCMSPlugin\Functional;

use AKAWAKA\SyliusCMSPlugin\Entity\Homepage;
use AKAWAKA\SyliusCMSPlugin\Enum\PageStatus;

final class HomepageTest extends FunctionalTestCase
{
    private const BLOCKS = [['id' => 'h1', 'type' => 'rich_text', 'props' => ['html' => '<p>Welcome to the CMS homepage</p>']]];

    public function testAPublishedHomepageReplacesTheSyliusOne(): void
    {
        $this->createHomepage(blocks: self::BLOCKS);

        $crawler = $this->client->request('GET', '/en_US/');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Welcome to the CMS homepage', $crawler->filter('[data-cms-block="h1"]')->text());
        self::assertSame('http://localhost/en_US/', $crawler->filter('link[rel="canonical"]')->attr('href'));
        self::assertCount(1, $crawler->filter('link[rel="alternate"][hreflang="en-US"]'));
        self::assertSame('Homepage description', $crawler->filter('meta[name="description"]')->attr('content'));
        self::assertSelectorNotExists('.cms-page + .cms-page');
    }

    public function testADraftHomepageFallsBackToTheSyliusOne(): void
    {
        $this->createHomepage(PageStatus::Draft, self::BLOCKS);

        $this->client->request('GET', '/en_US/');

        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('[data-cms-block="h1"]');
        self::assertSelectorNotExists('.cms-page');
    }

    public function testALocaleWithoutBlocksFallsBackToTheSyliusHomepage(): void
    {
        $this->createHomepage(blocks: []);

        $this->client->request('GET', '/en_US/');

        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('.cms-page');
    }

    public function testTheHomepageOfAnotherChannelIsIgnored(): void
    {
        $other = $this->otherChannel();
        $this->createHomepage(blocks: self::BLOCKS, channel: $other);

        $this->client->request('GET', '/en_US/');

        self::assertSelectorNotExists('[data-cms-block="h1"]');
    }

    public function testOtherRoutesAreNotAffected(): void
    {
        $this->createHomepage(blocks: self::BLOCKS);

        $this->client->request('GET', '/en_US/products/does-not-exist');

        self::assertResponseStatusCodeSame(404);
        self::assertSelectorNotExists('[data-cms-block="h1"]');
    }

    public function testTheHomepagePreviewRendersUnsavedBlocks(): void
    {
        $this->logInAsAdministrator();
        $token = $this->previewToken();

        $this->client->request('POST', '/admin/cms/preview', server: ['CONTENT_TYPE' => 'application/json', 'HTTP_X_CSRF_TOKEN' => $token], content: json_encode(['locale' => 'en_US', 'blocks' => self::BLOCKS], \JSON_THROW_ON_ERROR));
        $url = json_decode((string) $this->client->getResponse()->getContent(), true)['url'];

        $this->client->request('GET', $url);
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('[data-cms-block="h1"]');
    }

    public function testTheAdminCanBuildAndPublishTheHomepage(): void
    {
        $this->logInAsAdministrator();

        $crawler = $this->client->request('GET', '/admin/cms/homepages/new');
        self::assertResponseIsSuccessful();
        $form = $crawler->filter('form[name="akawaka_cms_homepage"]')->form();
        $form['akawaka_cms_homepage[channel]'] = (string) $this->channel()->getCode();
        $form['akawaka_cms_homepage[translations][en_US][blocks]'] = json_encode(self::BLOCKS, \JSON_THROW_ON_ERROR);
        $this->client->submit($form);
        self::assertResponseRedirects();

        $homepage = $this->entityManager()->getRepository(Homepage::class)->findOneBy([]);
        self::assertInstanceOf(Homepage::class, $homepage);
        self::assertSame(PageStatus::Draft, $homepage->getStatus());

        $crawler = $this->client->request('GET', '/admin/cms/homepages/');
        $publish = $crawler->filter('form[action$="/' . $homepage->getId() . '/publish"]');
        self::assertCount(1, $publish);
        $this->client->submit($publish->form());

        $this->client->request('GET', '/en_US/');
        self::assertSelectorExists('[data-cms-block="h1"]');
    }

    public function testAChannelCannotHaveTwoHomepages(): void
    {
        $this->createHomepage(blocks: self::BLOCKS);
        $this->logInAsAdministrator();

        $crawler = $this->client->request('GET', '/admin/cms/homepages/new');
        $form = $crawler->filter('form[name="akawaka_cms_homepage"]')->form();
        $form['akawaka_cms_homepage[channel]'] = (string) $this->channel()->getCode();
        $this->client->submit($form);

        self::assertFalse($this->client->getResponse()->isRedirect());
        self::assertCount(1, $this->entityManager()->getRepository(Homepage::class)->findAll());
    }

    public function testInvalidBlocksAreRefused(): void
    {
        $homepage = $this->createHomepage(blocks: self::BLOCKS);
        $this->logInAsAdministrator();

        $crawler = $this->client->request('GET', '/admin/cms/homepages/' . $homepage->getId() . '/edit');
        $form = $crawler->filter('form[name="akawaka_cms_homepage"]')->form();
        $form['akawaka_cms_homepage[translations][en_US][blocks]'] = '[{"id":"x","type":"nope"}]';
        $this->client->submit($form);

        self::assertFalse($this->client->getResponse()->isRedirect());
    }

    private function previewToken(): string
    {
        $homepage = $this->createHomepage(PageStatus::Draft, self::BLOCKS);
        $crawler = $this->client->request('GET', '/admin/cms/homepages/' . $homepage->getId() . '/edit');

        return (string) $crawler->filter('[data-akawaka-cms-preview-csrf-value]')->attr('data-akawaka-cms-preview-csrf-value');
    }
}
