<?php

declare(strict_types=1);

namespace Tests\Akawaka\SyliusCMSPlugin\Functional;

use Akawaka\SyliusCMSPlugin\Enum\PageStatus;

final class AdminPageAutocompleteTest extends FunctionalTestCase
{
    public function testAnonymousUsersCannotSearchPages(): void
    {
        $this->client->request('GET', '/admin/autocomplete/akawaka_cms_page?query=about');

        self::assertResponseRedirects();
    }

    public function testItSearchesPublishedPagesBySlugAndTitle(): void
    {
        $this->createPage('about', 'functional-about-us');
        $this->createPage('draft', 'functional-about-draft', PageStatus::Draft);
        $this->createPage('other', 'functional-contact');
        $this->logInAsAdministrator();

        $this->client->request('GET', '/admin/autocomplete/akawaka_cms_page?query=functional-about');

        self::assertResponseIsSuccessful();
        $texts = $this->resultTexts();
        self::assertCount(1, $texts);
        self::assertStringContainsString('/functional-about-us', $texts[0]);
    }

    public function testItSearchesPagesByCode(): void
    {
        $this->createPage('by_code', 'functional-some-slug');
        $this->logInAsAdministrator();

        $this->client->request('GET', '/admin/autocomplete/akawaka_cms_page?query=functional_by_code');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $this->resultTexts());
    }

    /** @return list<string> */
    private function resultTexts(): array
    {
        $data = json_decode((string) $this->client->getResponse()->getContent(), true, flags: \JSON_THROW_ON_ERROR);
        \assert(\is_array($data));

        return array_values(array_map(static fn (array $result): string => $result['text'], $data['results']));
    }
}
