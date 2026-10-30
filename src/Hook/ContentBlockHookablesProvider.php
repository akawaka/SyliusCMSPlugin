<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Hook;

use AKAWAKA\SyliusCMSPlugin\Entity\ContentBlockInterface;
use AKAWAKA\SyliusCMSPlugin\Entity\ContentBlockTranslationInterface;
use AKAWAKA\SyliusCMSPlugin\Repository\ContentBlockRepository;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Locale\Context\LocaleNotFoundException;
use Sylius\TwigHooks\Hookable\HookableTemplate;
use Symfony\Contracts\Service\ResetInterface;

/**
 * The published content blocks of the current channel and locale, as twig hookables indexed by hook name.
 * Loaded once per request (and reset between requests of long-running workers).
 */
final class ContentBlockHookablesProvider implements ResetInterface
{
    public const string TEMPLATE = '@AkawakaSyliusCMSPlugin/shop/hook/content_block.html.twig';

    public const string NAME_PREFIX = 'akawaka_cms_block_';

    /** @var array<string, list<HookableTemplate>>|null */
    private ?array $hookables = null;

    public function __construct(
        private readonly ContentBlockRepository $repository,
        private readonly ChannelContextInterface $channelContext,
        private readonly LocaleContextInterface $localeContext,
        private readonly HookPrefixes $prefixes,
    ) {
    }

    /**
     * @param list<string> $hookNames
     *
     * @return list<HookableTemplate>
     */
    public function forHooks(array $hookNames): array
    {
        if ([] === array_filter($hookNames, $this->prefixes->allows(...))) {
            return [];
        }

        $found = [];
        foreach ($hookNames as $hookName) {
            foreach ($this->load()[$hookName] ?? [] as $hookable) {
                $found[] = $hookable;
            }
        }

        return $found;
    }

    public function reset(): void
    {
        $this->hookables = null;
    }

    /** @return array<string, list<HookableTemplate>> */
    private function load(): array
    {
        if (null !== $this->hookables) {
            return $this->hookables;
        }

        $this->hookables = [];

        try {
            $channel = $this->channelContext->getChannel();
            $locale = $this->localeContext->getLocaleCode();
        } catch (ChannelNotFoundException|LocaleNotFoundException) {
            return $this->hookables;
        }

        foreach ($this->repository->findPublishedForChannel($channel) as $block) {
            $blocks = $this->blocksFor($block, $locale);
            if ([] === $blocks) {
                continue;
            }

            foreach ($block->getPlacements() as $placement) {
                $this->hookables[$placement['hook']][] = new HookableTemplate(
                    $placement['hook'],
                    self::NAME_PREFIX . $block->getCode(),
                    self::TEMPLATE,
                    ['cms_blocks' => $blocks, 'cms_block_code' => $block->getCode()],
                    [],
                    $placement['priority'],
                );
            }
        }

        return $this->hookables;
    }

    /** @return array<int, array<string, mixed>> */
    private function blocksFor(ContentBlockInterface $block, string $locale): array
    {
        /** @var ContentBlockTranslationInterface $translation */
        foreach ($block->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale) {
                return $translation->getBlocks();
            }
        }

        return [];
    }
}
