<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Entity;

use Akawaka\SyliusCMSPlugin\Entity\Concern\BlocksTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\AbstractTranslation;

#[ORM\Entity]
#[ORM\Table(name: 'akawaka_cms_content_block_translation')]
#[ORM\UniqueConstraint(name: 'akawaka_cms_content_block_translation_unique', columns: ['translatable_id', 'locale'])]
class ContentBlockTranslation extends AbstractTranslation implements ContentBlockTranslationInterface
{
    use BlocksTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
