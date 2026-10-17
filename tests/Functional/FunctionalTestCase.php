<?php

declare(strict_types=1);

namespace Tests\Akawaka\SyliusCMSPlugin\Functional;

use Akawaka\SyliusCMSPlugin\Entity\ContentBlock;
use Akawaka\SyliusCMSPlugin\Entity\ContentBlockTranslation;
use Akawaka\SyliusCMSPlugin\Entity\Homepage;
use Akawaka\SyliusCMSPlugin\Entity\HomepageTranslation;
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

    /** @param list<array<string, mixed>> $blocks */
    protected function createHomepage(PageStatus $status = PageStatus::Published, array $blocks = [], string $locale = 'en_US', ?ChannelInterface $channel = null): Homepage
    {
        $homepage = new Homepage();
        $homepage->setChannel($channel ?? $this->channel());
        $homepage->setStatus($status);

        $homepage->setCurrentLocale($locale);
        $homepage->setFallbackLocale($locale);
        $translation = $homepage->getTranslation($locale);
        \assert($translation instanceof HomepageTranslation);
        $translation->setBlocks($blocks);
        $translation->setMetaDescription('Homepage description');

        $this->entityManager()->persist($homepage);
        $this->entityManager()->flush();

        return $homepage;
    }

    /**
     * @param list<array{hook: string, priority: int}> $placements
     * @param list<array<string, mixed>> $blocks
     * @param list<ChannelInterface> $channels
     */
    protected function createContentBlock(string $code, array $placements, PageStatus $status = PageStatus::Published, array $blocks = [], array $channels = [], string $locale = 'en_US'): ContentBlock
    {
        $block = new ContentBlock();
        $block->setCode(self::CODE_PREFIX . $code);
        $block->setName('Block ' . $code);
        $block->setStatus($status);
        $block->setPlacements($placements);
        foreach ($channels as $channel) {
            $block->addChannel($channel);
        }

        $block->setCurrentLocale($locale);
        $block->setFallbackLocale($locale);
        $translation = $block->getTranslation($locale);
        \assert($translation instanceof ContentBlockTranslation);
        $translation->setBlocks($blocks ?: [['id' => $code, 'type' => 'rich_text', 'props' => ['html' => '<p>Content of ' . $code . '</p>']]]);

        $this->entityManager()->persist($block);
        $this->entityManager()->flush();

        return $block;
    }

    protected function channel(): ChannelInterface
    {
        $channel = static::getContainer()->get('sylius.repository.channel');
        \assert($channel instanceof ChannelRepositoryInterface);

        $first = $channel->findOneBy([]);
        \assert($first instanceof ChannelInterface);

        return $first;
    }

    protected function otherChannel(): ChannelInterface
    {
        $main = $this->channel();
        $channel = static::getContainer()->get('sylius.factory.channel')->createNew();
        \assert($channel instanceof ChannelInterface);
        $channel->setCode(self::CODE_PREFIX . 'other');
        $channel->setName('Functional other channel');
        $channel->setBaseCurrency($main->getBaseCurrency());
        $channel->setDefaultLocale($main->getDefaultLocale());
        $channel->setTaxCalculationStrategy($main->getTaxCalculationStrategy());
        $this->entityManager()->persist($channel);
        $this->entityManager()->flush();

        return $channel;
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

        foreach ($em->getRepository(Homepage::class)->findAll() as $homepage) {
            $em->remove($homepage);
        }

        foreach ($em->getRepository(ContentBlock::class)->findAll() as $block) {
            if (str_starts_with((string) $block->getCode(), self::CODE_PREFIX)) {
                $em->remove($block);
            }
        }

        foreach ($em->getRepository(ChannelInterface::class)->findAll() as $channel) {
            if (str_starts_with((string) $channel->getCode(), self::CODE_PREFIX)) {
                $em->remove($channel);
            }
        }

        $em->flush();
    }
}
