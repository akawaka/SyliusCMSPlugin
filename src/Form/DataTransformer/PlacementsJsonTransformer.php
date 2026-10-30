<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Form\DataTransformer;

use Symfony\Component\Form\CallbackTransformer;

/** Edits the hook placements of a content block as a JSON string (written by the hook picker). */
final class PlacementsJsonTransformer extends CallbackTransformer
{
    public function __construct()
    {
        parent::__construct(
            static fn (?array $placements): string => json_encode($placements ?? [], \JSON_THROW_ON_ERROR),
            static function (?string $json): array {
                $decoded = json_decode($json ?: '[]', true);

                return \is_array($decoded) ? $decoded : [];
            },
        );
    }
}
