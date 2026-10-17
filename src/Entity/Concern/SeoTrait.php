<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Entity\Concern;

use Doctrine\ORM\Mapping as ORM;

/** Search engine and social sharing fields of a translation. */
trait SeoTrait
{
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $metaTitle = null;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $metaDescription = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $ogImage = null;

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
