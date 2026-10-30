<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Form\DataTransformer;

use Symfony\Component\Form\CallbackTransformer;

/** Edits the block tree of a translation as a JSON string (the page builder reads and writes this field). */
final class BlocksJsonTransformer extends CallbackTransformer
{
    public function __construct()
    {
        parent::__construct(
            static fn (?array $blocks): string => json_encode($blocks ?? [], \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_THROW_ON_ERROR),
            static function (?string $json): array {
                $decoded = json_decode($json ?: '[]', true);

                return \is_array($decoded) ? $decoded : [];
            },
        );
    }
}
