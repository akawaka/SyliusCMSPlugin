<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
final class ValidBlockTree extends Constraint
{
    public string $message = 'akawaka_cms.block_tree.violation';
}
