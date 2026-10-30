<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Repository;

use AKAWAKA\SyliusCMSPlugin\Entity\ContentBlockInterface;
use AKAWAKA\SyliusCMSPlugin\Enum\PageStatus;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Channel\Model\ChannelInterface;

class ContentBlockRepository extends EntityRepository
{
    public function createListQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('o');
    }

    /**
     * Published blocks displayed on the channel (those without channel restriction included), with their translations.
     *
     * @return list<ContentBlockInterface>
     */
    public function findPublishedForChannel(ChannelInterface $channel): array
    {
        /** @var list<ContentBlockInterface> $blocks */
        $blocks = $this->createQueryBuilder('o')
            ->addSelect('translation', 'channel')
            ->leftJoin('o.translations', 'translation')
            ->leftJoin('o.channels', 'channel')
            ->andWhere('o.status = :status')
            ->andWhere('o.id NOT IN (SELECT b.id FROM ' . $this->getEntityName() . ' b JOIN b.channels c) OR :channel MEMBER OF o.channels')
            ->setParameter('status', PageStatus::Published->value)
            ->setParameter('channel', $channel)
            ->orderBy('o.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        return array_values(array_filter($blocks, static fn (ContentBlockInterface $block): bool => $block->appliesToChannel($channel)));
    }
}
