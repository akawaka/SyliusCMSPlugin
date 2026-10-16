<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Block\Type;

use Akawaka\SyliusCMSPlugin\Block\AbstractBlockType;
use Akawaka\SyliusCMSPlugin\Enum\BlockType;
use Akawaka\SyliusCMSPlugin\Form\Type\Block\ProductGridBlockType as ProductGridForm;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;

final class ProductGridBlockType extends AbstractBlockType
{
    /**
     * @param ProductRepositoryInterface<ProductInterface> $productRepository
     * @param TaxonRepositoryInterface<TaxonInterface> $taxonRepository
     */
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly TaxonRepositoryInterface $taxonRepository,
        private readonly ChannelContextInterface $channelContext,
        private readonly LocaleContextInterface $localeContext,
    ) {
    }

    public function getCode(): string
    {
        return BlockType::ProductGrid->value;
    }

    public function getIcon(): string
    {
        return 'tabler:layout-grid';
    }

    public function getFormType(): string
    {
        return ProductGridForm::class;
    }

    public function getDefaults(): array
    {
        return ['taxon' => '', 'limit' => 4, 'columns' => 4];
    }

    public function getViewData(array $props): array
    {
        return ['products' => $this->findProducts($props)];
    }

    /**
     * @param array<string, mixed> $props
     *
     * @return array<int, ProductInterface>
     */
    private function findProducts(array $props): array
    {
        try {
            $channel = $this->channelContext->getChannel();
        } catch (ChannelNotFoundException) {
            return [];
        }

        if (!$channel instanceof ChannelInterface) {
            return [];
        }

        $locale = $this->localeContext->getLocaleCode();
        $rawLimit = $props['limit'] ?? 4;
        $limit = max(1, is_numeric($rawLimit) ? (int) $rawLimit : 4);
        $taxonCode = $props['taxon'] ?? null;

        if (!\is_string($taxonCode) || '' === $taxonCode) {
            /** @var array<int, ProductInterface> $products */
            $products = $this->productRepository->findLatestByChannel($channel, $locale, $limit);

            return $products;
        }

        $taxon = $this->taxonRepository->findOneBy(['code' => $taxonCode]);
        if (!$taxon instanceof TaxonInterface) {
            return [];
        }

        /** @var array<int, ProductInterface> $products */
        $products = $this->productRepository
            ->createShopListQueryBuilder($channel, $taxon, $locale, [], true)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;

        return $products;
    }
}
