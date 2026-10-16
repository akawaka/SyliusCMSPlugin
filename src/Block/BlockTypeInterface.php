<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Block;

use Symfony\Component\Form\FormTypeInterface;

interface BlockTypeInterface
{
    /** Unique identifier stored in the JSON tree (e.g. "rich_text"). */
    public function getCode(): string;

    /** Translation key of the label shown in the builder palette. */
    public function getLabel(): string;

    /** Tabler icon name shown in the builder palette. */
    public function getIcon(): string;

    /** @return class-string<FormTypeInterface> form describing and validating the block props */
    public function getFormType(): string;

    /** Twig template rendering the block on the storefront. */
    public function getTemplate(): string;

    /** @return array<string, mixed> */
    public function getDefaults(): array;

    /**
     * Extra data made available to the template as `data`.
     *
     * @param array<string, mixed> $props props already merged with the defaults
     *
     * @return array<string, mixed>
     */
    public function getViewData(array $props): array;

    public function acceptsChild(string $code): bool;

    /** Internal blocks cannot be added by the user (only created by their parent). */
    public function isInternal(): bool;
}
