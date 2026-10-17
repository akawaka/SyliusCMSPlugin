<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/** The twig hooks a content block is attached to: [{"hook": "sylius_shop...", "priority": 0}, ...]. */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
final class ValidPlacements extends Constraint
{
    public const int MAX_PLACEMENTS = 20;

    public const int MIN_PRIORITY = -1000;

    public const int MAX_PRIORITY = 1000;

    public string $message = 'akawaka_cms.content_block.placements.invalid';
}
