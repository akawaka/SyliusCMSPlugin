<?php

declare(strict_types=1);

namespace Tests\Akawaka\SyliusCMSPlugin\Functional;

use Akawaka\SyliusCMSPlugin\Entity\Page;
use Akawaka\SyliusCMSPlugin\Entity\PageTranslation;
use Akawaka\SyliusCMSPlugin\Enum\PageStatus;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Repository\ChannelRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/** Boots the test application; requires the test database to be created, migrated and loaded with fixtures. */
abstract class FunctionalTestCase extends WebTestCase
{
    protected const CODE_PREFIX = 'functional_';

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->purge();
    }

    protected function tearDown(): void
    {
        $this->purge();

        parent::tearDown();
    }

    protected function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get('doctrine.orm.entity_manager');
    }

    /** @param list<array<string, mixed>> $blocks */
    protected function createPage(string $code, string $slug, PageStatus $status = PageStatus::Published, array $blocks = [], string $locale = 'en_US'): Page
    {
        $page = new Page();
        $page->setCode(self::CODE_PREFIX . $code);
        $page->setStatus($status);
        $page->addChannel($this->channel());

        $page->setCurrentLocale($locale);
        $page->setFallbackLocale($locale);
        $translation = $page->getTranslation($locale);
        \assert($translation instanceof PageTranslation);
        $translation->setTitle('Title ' . $code);
        $translation->setSlug($slug);
        $translation->setBlocks($blocks);
        $translation->setMetaDescription('Description ' . $code);

        $this->entityManager()->persist($page);
        $this->entityManager()->flush();

        return $page;
    }

    protected function channel(): ChannelInterface
    {
        $channel = static::getContainer()->get('sylius.repository.channel');
        \assert($channel instanceof ChannelRepositoryInterface);

        $first = $channel->findOneBy([]);
        \assert($first instanceof ChannelInterface);

        return $first;
    }

    protected function logInAsAdministrator(): void
    {
        $crawler = $this->client->request('GET', '/admin/login');
        $this->client->submit($crawler->selectButton('Login')->form(['_username' => 'sylius', '_password' => 'sylius']));
        $this->client->followRedirect();
    }

    private function purge(): void
    {
        $em = $this->entityManager();
        $em->clear();

        foreach ($em->getRepository(Page::class)->findAll() as $page) {
            if (str_starts_with((string) $page->getCode(), self::CODE_PREFIX)) {
                $em->remove($page);
            }
        }

        $em->flush();
    }
}
