<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Entity;

use Akawaka\SyliusCMSPlugin\Enum\PageStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;
use Sylius\Component\Resource\Model\TranslationInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'akawaka_cms_page')]
#[UniqueEntity(fields: ['code'], message: 'akawaka_cms.page.code.unique', groups: ['akawaka_cms'])]
class Page implements PageInterface
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
    #[Assert\Regex(pattern: '/^[a-z0-9_-]+$/i', message: 'akawaka_cms.page.code.regex', groups: ['akawaka_cms'])]
    protected ?string $code = null;

    #[ORM\Column(type: 'string', length: 16, enumType: PageStatus::class)]
    protected PageStatus $status = PageStatus::Draft;

    /** @var Collection<int, ChannelInterface> */
    #[ORM\ManyToMany(targetEntity: ChannelInterface::class)]
    #[ORM\JoinTable(name: 'akawaka_cms_page_channels')]
    #[ORM\JoinColumn(name: 'page_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'channel_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected Collection $channels;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    protected ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    protected ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->initializeTranslationsCollection();

        $this->channels = new ArrayCollection();
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

    #[Assert\Callback(groups: ['akawaka_cms'])]
    public function validateHasTranslation(ExecutionContextInterface $context): void
    {
        /** @var PageTranslationInterface $translation */
        foreach ($this->translations as $translation) {
            if (null !== $translation->getTitle() && '' !== $translation->getTitle()) {
                return;
            }
        }

        $context->buildViolation('akawaka_cms.page.translation.at_least_one')->atPath('translations')->addViolation();
    }

    public function getTitle(): ?string
    {
        return $this->getPageTranslation()->getTitle();
    }

    public function getSlug(): ?string
    {
        return $this->getPageTranslation()->getSlug();
    }

    public function getPageTranslation(?string $locale = null): PageTranslationInterface
    {
        /** @var PageTranslationInterface $translation */
        $translation = $this->doGetTranslation($locale);

        return $translation;
    }

    protected function createTranslation(): TranslationInterface
    {
        return new PageTranslation();
    }
}
