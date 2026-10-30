<?php

declare(strict_types=1);

namespace Tests\AKAWAKA\SyliusCMSPlugin\Functional;

use AKAWAKA\SyliusCMSPlugin\Enum\PageStatus;

final class PublicationButtonTest extends FunctionalTestCase
{
    /** @dataProvider resources */
    public function testADraftOffersToPublishAndAPublishedResourceToUnpublish(string $resource): void
    {
        $this->logInAsAdministrator();
        $draft = $this->create($resource, PageStatus::Draft);

        $crawler = $this->client->request('GET', $this->editUrl($resource, $draft->getId()));
        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('form[action$="/' . $draft->getId() . '/publish"]'));
        self::assertCount(0, $crawler->filter('form[action$="/unpublish"]'));

        $published = $this->create($resource . '_pub', PageStatus::Published, $resource);
        $crawler = $this->client->request('GET', $this->editUrl($resource, $published->getId()));
        self::assertCount(1, $crawler->filter('form[action$="/' . $published->getId() . '/unpublish"]'));
        self::assertCount(0, $crawler->filter('form[action$="/publish"]'));
    }

    /** @dataProvider resources */
    public function testPublishingFromTheEditPageComesBackToIt(string $resource): void
    {
        $this->logInAsAdministrator();
        $item = $this->create($resource, PageStatus::Draft);
        $editUrl = $this->editUrl($resource, $item->getId());

        $crawler = $this->client->request('GET', $editUrl);
        $this->client->setServerParameter('HTTP_REFERER', 'http://localhost' . $editUrl);
        $this->client->submit($crawler->filter('form[action$="/' . $item->getId() . '/publish"]')->form());
        self::assertResponseRedirects('http://localhost' . $editUrl);

        $this->entityManager()->clear();
        self::assertTrue($this->entityManager()->find($item::class, $item->getId())->isPublished());

        $crawler = $this->client->request('GET', $editUrl);
        $this->client->submit($crawler->filter('form[action$="/' . $item->getId() . '/unpublish"]')->form());
        $this->entityManager()->clear();
        self::assertFalse($this->entityManager()->find($item::class, $item->getId())->isPublished());
    }

    /** @dataProvider resources */
    public function testThePublicationFromTheListComesBackToTheList(string $resource): void
    {
        $this->logInAsAdministrator();
        $item = $this->create($resource, PageStatus::Draft);
        $listUrl = $this->listUrl($resource);

        $crawler = $this->client->request('GET', $listUrl);
        $this->client->setServerParameter('HTTP_REFERER', 'http://localhost' . $listUrl);
        $this->client->submit($crawler->filter('form[action$="/' . $item->getId() . '/publish"]')->form());

        self::assertResponseRedirects('http://localhost' . $listUrl);
    }

    /** @dataProvider resources */
    public function testTheCreationPageHasNoPublicationButton(string $resource): void
    {
        $this->logInAsAdministrator();

        $crawler = $this->client->request('GET', $this->listUrl($resource) . 'new');

        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('form[action$="/publish"]'));
    }

    /** @return iterable<string, array{string}> */
    public static function resources(): iterable
    {
        yield 'page' => ['page'];
        yield 'homepage' => ['homepage'];
        yield 'content block' => ['content_block'];
    }

    private function create(string $key, PageStatus $status, ?string $resource = null): object
    {
        return match ($resource ?? $key) {
            'page' => $this->createPage($key, 'functional-' . $key, $status),
            'homepage' => $this->createHomepage($status, [['id' => 'h', 'type' => 'rich_text', 'props' => ['html' => '<p>x</p>']]], channel: $this->extraChannel($key)),
            default => $this->createContentBlock($key, [], $status),
        };
    }

    /** One homepage per channel: the second one of a test goes to another channel. */
    private function extraChannel(string $key): ?\Sylius\Component\Core\Model\ChannelInterface
    {
        return str_ends_with($key, '_pub') ? $this->otherChannel() : null;
    }

    private function editUrl(string $resource, ?int $id): string
    {
        return $this->listUrl($resource) . $id . '/edit';
    }

    private function listUrl(string $resource): string
    {
        return match ($resource) {
            'page' => '/admin/cms/pages/',
            'homepage' => '/admin/cms/homepages/',
            default => '/admin/cms/content-blocks/',
        };
    }
}
