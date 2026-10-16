<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Repository;

use Akawaka\SyliusCMSPlugin\Entity\PageInterface;
use Akawaka\SyliusCMSPlugin\Enum\PageStatus;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Channel\Model\ChannelInterface;

class PageRepository extends EntityRepository
{
    public function createListQueryBuilder(string $locale): QueryBuilder
    {
        return $this->createQueryBuilder('o')
            ->addSelect('translation')
            ->leftJoin('o.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->setParameter('locale', $locale)
        ;
    }

    public function findOnePublishedBySlug(string $slug, string $locale, ChannelInterface $channel): ?PageInterface
    {
        $page = $this->createQueryBuilder('o')
            ->addSelect('translation')
            ->innerJoin('o.translations', 'translation', 'WITH', 'translation.locale = :locale AND translation.slug = :slug')
            ->innerJoin('o.channels', 'channel', 'WITH', 'channel = :channel')
            ->andWhere('o.status = :status')
            ->setParameter('locale', $locale)
            ->setParameter('slug', $slug)
            ->setParameter('channel', $channel)
            ->setParameter('status', PageStatus::Published->value)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $page instanceof PageInterface ? $page : null;
    }
}
