<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Entity\Concern;

use Akawaka\SyliusCMSPlugin\Validator\Constraint\ValidBlockTree;
use Doctrine\ORM\Mapping as ORM;

/** Block tree and SEO fields shared by the translations of pages and of the homepage. */
trait BlocksContentTrait
{
    /** @var array<int, array<string, mixed>> */
    #[ORM\Column(type: 'json')]
    #[ValidBlockTree(groups: ['akawaka_cms'])]
    protected array $blocks = [];

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $metaTitle = null;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $metaDescription = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $ogImage = null;

    public function getBlocks(): array
    {
        return $this->blocks;
    }

    public function setBlocks(array $blocks): void
    {
        $this->blocks = $blocks;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): void
    {
        $this->metaTitle = $metaTitle;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    public function getOgImage(): ?string
    {
        return $this->ogImage;
    }

    public function setOgImage(?string $ogImage): void
    {
        $this->ogImage = $ogImage;
    }
}
