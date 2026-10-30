<?php

declare(strict_types=1);

namespace Tests\AKAWAKA\SyliusCMSPlugin\Functional;

final class AdminPreviewTest extends FunctionalTestCase
{
    public function testAnonymousUsersCannotUseTheAdminEndpoints(): void
    {
        $this->client->request('POST', '/admin/cms/preview');
        self::assertResponseRedirects();

        $this->client->request('GET', '/admin/cms/blocks/image/form');
        self::assertResponseRedirects();
    }

    public function testThePreviewRendersUnsavedContentOnTheStorefront(): void
    {
        $this->logInAsAdministrator();
        $blocks = [['id' => 'p1', 'type' => 'rich_text', 'props' => ['html' => '<p>Unsaved draft content</p>']]];

        $url = $this->postPreview(['locale' => 'en_US', 'blocks' => $blocks, 'page' => ['title' => 'Not saved yet']]);

        $crawler = $this->client->request('GET', $url);
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Unsaved draft content', $crawler->filter('[data-cms-block="p1"]')->text());
        self::assertStringContainsString('noindex', $crawler->filter('meta[name="robots"]')->attr('content') ?? '');
    }

    public function testThePreviewRejectsAMissingCsrfToken(): void
    {
        $this->logInAsAdministrator();

        $this->client->request('POST', '/admin/cms/preview', server: ['CONTENT_TYPE' => 'application/json'], content: '{"locale":"en_US","blocks":[]}');

        self::assertResponseStatusCodeSame(403);
    }

    public function testThePreviewRejectsAnInvalidLocale(): void
    {
        $this->logInAsAdministrator();

        $this->client->request('POST', '/admin/cms/preview', server: ['CONTENT_TYPE' => 'application/json', 'HTTP_X_CSRF_TOKEN' => $this->csrfToken()], content: '{"locale":"../etc","blocks":[]}');

        self::assertResponseStatusCodeSame(400);
    }

    public function testThePreviewReportsInvalidBlocksAsWarnings(): void
    {
        $this->logInAsAdministrator();

        $this->postPreview(['locale' => 'en_US', 'blocks' => [['id' => 'x', 'type' => 'nope']]]);

        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertIsArray($data);
        self::assertNotEmpty($data['warnings']);
    }

    public function testTheBlockSettingsFormIsRendered(): void
    {
        $this->logInAsAdministrator();

        $crawler = $this->client->request('GET', '/admin/cms/blocks/image/form');

        self::assertResponseIsSuccessful();
        self::assertGreaterThan(0, $crawler->filter('[name^="props["]')->count());
    }

    public function testAnUnknownBlockTypeIsNotFound(): void
    {
        $this->logInAsAdministrator();

        $this->client->request('GET', '/admin/cms/blocks/unknown/form');

        self::assertResponseStatusCodeSame(404);
    }

    /** @param array<string, mixed> $payload */
    private function postPreview(array $payload): string
    {
        $this->client->request('POST', '/admin/cms/preview', server: ['CONTENT_TYPE' => 'application/json', 'HTTP_X_CSRF_TOKEN' => $this->csrfToken()], content: json_encode($payload, \JSON_THROW_ON_ERROR));
        self::assertResponseIsSuccessful();

        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertIsArray($data);
        self::assertIsString($data['url']);

        return $data['url'];
    }

    /** The token is rendered in the builder of the page edit screen, as the real front-end gets it. */
    private function csrfToken(): string
    {
        $page = $this->createPage('csrf', 'functional-csrf');
        $crawler = $this->client->request('GET', '/admin/cms/pages/' . $page->getId() . '/edit');
        self::assertResponseIsSuccessful();

        return (string) $crawler->filter('[data-akawaka-cms-preview-csrf-value]')->attr('data-akawaka-cms-preview-csrf-value');
    }
}
