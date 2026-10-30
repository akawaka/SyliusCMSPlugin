<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Entity;

use AKAWAKA\SyliusCMSPlugin\Entity\Concern\BlocksContentTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\AbstractTranslation;

#[ORM\Entity]
#[ORM\Table(name: 'akawaka_cms_homepage_translation')]
#[ORM\UniqueConstraint(name: 'akawaka_cms_homepage_translation_unique', columns: ['translatable_id', 'locale'])]
class HomepageTranslation extends AbstractTranslation implements HomepageTranslationInterface
{
    use BlocksContentTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
