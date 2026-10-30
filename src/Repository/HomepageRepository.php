<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Repository;

use AKAWAKA\SyliusCMSPlugin\Entity\HomepageInterface;
use AKAWAKA\SyliusCMSPlugin\Enum\PageStatus;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Channel\Model\ChannelInterface;

class HomepageRepository extends EntityRepository
{
    public function createListQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.channel', 'channel')
            ->addSelect('channel')
        ;
    }

    public function findOnePublishedForChannel(ChannelInterface $channel): ?HomepageInterface
    {
        $homepage = $this->createQueryBuilder('o')
            ->addSelect('translation')
            ->leftJoin('o.translations', 'translation')
            ->andWhere('o.channel = :channel')
            ->andWhere('o.status = :status')
            ->setParameter('channel', $channel)
            ->setParameter('status', PageStatus::Published->value)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $homepage instanceof HomepageInterface ? $homepage : null;
    }
}
