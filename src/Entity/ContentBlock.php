<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Entity;

use AKAWAKA\SyliusCMSPlugin\Enum\PageStatus;
use AKAWAKA\SyliusCMSPlugin\Validator\Constraint\ValidPlacements;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;
use Sylius\Component\Resource\Model\TranslationInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'akawaka_cms_content_block')]
#[UniqueEntity(fields: ['code'], message: 'akawaka_cms.content_block.code.unique', groups: ['akawaka_cms'])]
class ContentBlock implements ContentBlockInterface
{
    use TranslatableTrait {
        __construct as private initializeTranslationsCollection;
        getTranslation as private doGetTranslation;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank(groups: ['akawaka_cms'])]
    #[Assert\Regex(pattern: '/^[a-z0-9_-]+$/i', message: 'akawaka_cms.content_block.code.regex', groups: ['akawaka_cms'])]
    protected ?string $code = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(groups: ['akawaka_cms'])]
    protected ?string $name = null;

    #[ORM\Column(type: 'string', length: 16, enumType: PageStatus::class)]
    protected PageStatus $status = PageStatus::Draft;

    /** @var Collection<int, ChannelInterface> */
    #[ORM\ManyToMany(targetEntity: ChannelInterface::class)]
    #[ORM\JoinTable(name: 'akawaka_cms_content_block_channels')]
    #[ORM\JoinColumn(name: 'content_block_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'channel_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected Collection $channels;

    /** @var list<array{hook: string, priority: int}> */
    #[ORM\Column(type: 'json')]
    #[ValidPlacements(groups: ['akawaka_cms'])]
    protected array $placements = [];

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    protected ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    protected ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->initializeTranslationsCollection();

        $this->channels = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt ??= new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getStatus(): PageStatus
    {
        return $this->status;
    }

    public function setStatus(PageStatus $status): void
    {
        $this->status = $status;
    }

    public function isPublished(): bool
    {
        return PageStatus::Published === $this->status;
    }

    /** @return Collection<int, ChannelInterface> */
    public function getChannels(): Collection
    {
        return $this->channels;
    }

    public function addChannel(ChannelInterface $channel): void
    {
        if (!$this->channels->contains($channel)) {
            $this->channels->add($channel);
        }
    }

    public function removeChannel(ChannelInterface $channel): void
    {
        $this->channels->removeElement($channel);
    }

    public function appliesToChannel(ChannelInterface $channel): bool
    {
        return $this->channels->isEmpty() || $this->channels->contains($channel);
    }

    public function getPlacements(): array
    {
        return $this->placements;
    }

    public function setPlacements(array $placements): void
    {
        $this->placements = $placements;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getContentBlockTranslation(?string $locale = null): ContentBlockTranslationInterface
    {
        /** @var ContentBlockTranslationInterface $translation */
        $translation = $this->doGetTranslation($locale);

        return $translation;
    }

    protected function createTranslation(): TranslationInterface
    {
        return new ContentBlockTranslation();
    }
}
