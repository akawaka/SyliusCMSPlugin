<?php

declare(strict_types=1);

namespace Tests\Akawaka\SyliusCMSPlugin\Functional;

use Akawaka\SyliusCMSPlugin\Entity\Page;

final class AdminPageFormTest extends FunctionalTestCase
{
    public function testValidBlocksAreSaved(): void
    {
        $page = $this->createPage('save', 'functional-save');
        $blocks = [['id' => 'b1', 'type' => 'rich_text', 'props' => ['html' => '<p>Saved</p>']]];

        $this->submitEdit($page, ['translations[en_US][blocks]' => json_encode($blocks, \JSON_THROW_ON_ERROR)]);

        self::assertResponseRedirects();
        self::assertSame('b1', $this->reload($page)->getPageTranslation('en_US')->getBlocks()[0]['id'] ?? null);
    }

    public function testInvalidBlocksAreRefusedAndNothingIsSaved(): void
    {
        $page = $this->createPage('invalid', 'functional-invalid', blocks: [['id' => 'keep', 'type' => 'rich_text', 'props' => ['html' => '<p>Kept</p>']]]);
        $invalid = [['id' => 'x', 'type' => 'unknown_type']];

        $this->submitEdit($page, ['translations[en_US][blocks]' => json_encode($invalid, \JSON_THROW_ON_ERROR)]);

        self::assertFalse($this->client->getResponse()->isRedirect(), 'the form is displayed again with its errors');
        self::assertSame('keep', $this->reload($page)->getPageTranslation('en_US')->getBlocks()[0]['id'] ?? null);
    }

    public function testASlugAlreadyUsedInTheSameLocaleIsRefused(): void
    {
        $this->createPage('first', 'functional-taken');
        $page = $this->createPage('second', 'functional-free');

        $this->submitEdit($page, ['translations[en_US][slug]' => 'functional-taken']);

        self::assertFalse($this->client->getResponse()->isRedirect());
        self::assertSame('functional-free', $this->reload($page)->getPageTranslation('en_US')->getSlug());
    }

    public function testASlugWithInvalidCharactersIsRefused(): void
    {
        $page = $this->createPage('badslug', 'functional-badslug');

        $this->submitEdit($page, ['translations[en_US][slug]' => 'Not A Slug!']);

        self::assertFalse($this->client->getResponse()->isRedirect());
    }

    /** @param array<string, string> $values field paths relative to the form, e.g. "translations[en_US][slug]" */
    private function submitEdit(Page $page, array $values): void
    {
        $this->logInAsAdministrator();
        $crawler = $this->client->request('GET', '/admin/cms/pages/' . $page->getId() . '/edit');
        $form = $crawler->filter('form[name="akawaka_cms_page"]')->form();

        foreach ($values as $path => $value) {
            $form['akawaka_cms_page[' . strtok($path, '[') . ']' . substr($path, (int) strpos($path, '['))] = $value;
        }

        $this->client->submit($form);
    }

    private function reload(Page $page): Page
    {
        $em = $this->entityManager();
        $em->clear();

        $reloaded = $em->find(Page::class, $page->getId());
        \assert($reloaded instanceof Page);
        $reloaded->setCurrentLocale('en_US');

        return $reloaded;
    }
}
