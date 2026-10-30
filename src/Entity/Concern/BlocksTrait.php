<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Entity\Concern;

use AKAWAKA\SyliusCMSPlugin\Validator\Constraint\ValidBlockTree;
use Doctrine\ORM\Mapping as ORM;

/** The block tree of a translation. */
trait BlocksTrait
{
    /** @var array<int, array<string, mixed>> */
    #[ORM\Column(type: 'json')]
    #[ValidBlockTree(groups: ['akawaka_cms'])]
    protected array $blocks = [];

    public function getBlocks(): array
    {
        return $this->blocks;
    }

    public function setBlocks(array $blocks): void
    {
        $this->blocks = $blocks;
    }
}
