<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Validator;

final readonly class BlockTreeViolation
{
    /** @param array<string, string> $parameters */
    public function __construct(
        public string $message,
        public string $path,
        public array $parameters = [],
    ) {
    }
}
