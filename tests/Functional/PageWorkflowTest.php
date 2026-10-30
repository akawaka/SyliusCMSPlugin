<?php

declare(strict_types=1);

namespace Tests\AKAWAKA\SyliusCMSPlugin\Functional;

use AKAWAKA\SyliusCMSPlugin\Enum\PageStatus;
use AKAWAKA\SyliusCMSPlugin\Enum\PageTransition;
use Symfony\Component\Workflow\Registry;

final class PageWorkflowTest extends FunctionalTestCase
{
    public function testAPageIsPublishedThenUnpublished(): void
    {
        $page = $this->createPage('workflow', 'functional-workflow', PageStatus::Draft);
        $workflow = $this->registry()->get($page, 'akawaka_cms_page');

        self::assertTrue($workflow->can($page, PageTransition::Publish->value));
        self::assertFalse($workflow->can($page, PageTransition::Unpublish->value));

        $workflow->apply($page, PageTransition::Publish->value);
        self::assertSame(PageStatus::Published, $page->getStatus());
        self::assertFalse($workflow->can($page, PageTransition::Publish->value));

        $workflow->apply($page, PageTransition::Unpublish->value);
        self::assertSame(PageStatus::Draft, $page->getStatus());
    }

    public function testThePublishRouteMakesThePageVisibleOnTheStorefront(): void
    {
        $this->logInAsAdministrator();
        $page = $this->createPage('route', 'functional-route', PageStatus::Draft);

        $this->client->request('GET', '/en_US/pages/functional-route');
        self::assertResponseStatusCodeSame(404);

        $crawler = $this->client->request('GET', '/admin/cms/pages/');
        $publish = $crawler->filter('form[action$="/' . $page->getId() . '/publish"]');
        self::assertCount(1, $publish, 'the grid offers the publish action for a draft');
        $this->client->submit($publish->form());

        $this->client->request('GET', '/en_US/pages/functional-route');
        self::assertResponseIsSuccessful();
    }

    private function registry(): Registry
    {
        $registry = static::getContainer()->get('workflow.registry');
        \assert($registry instanceof Registry);

        return $registry;
    }
}
