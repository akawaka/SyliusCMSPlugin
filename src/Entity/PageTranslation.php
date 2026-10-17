<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Entity;

use Akawaka\SyliusCMSPlugin\Entity\Concern\BlocksContentTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\AbstractTranslation;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'akawaka_cms_page_translation')]
#[ORM\UniqueConstraint(name: 'akawaka_cms_page_slug_locale', columns: ['locale', 'slug'])]
#[ORM\UniqueConstraint(name: 'akawaka_cms_page_translation_unique', columns: ['translatable_id', 'locale'])]
#[UniqueEntity(fields: ['locale', 'slug'], message: 'akawaka_cms.page.slug.unique', errorPath: 'slug', ignoreNull: true, groups: ['akawaka_cms'])]
class PageTranslation extends AbstractTranslation implements PageTranslationInterface
{
    use BlocksContentTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $title = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Regex(pattern: '/^[a-z0-9]+(?:[\/-][a-z0-9]+)*$/', message: 'akawaka_cms.page.slug.regex', groups: ['akawaka_cms'])]
    protected ?string $slug = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }
}
