<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Validator\Constraint;

use AKAWAKA\SyliusCMSPlugin\Hook\HookPrefixes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class ValidPlacementsValidator extends ConstraintValidator
{
    public function __construct(private readonly HookPrefixes $prefixes)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidPlacements) {
            throw new UnexpectedTypeException($constraint, ValidPlacements::class);
        }

        if (null === $value || [] === $value) {
            return;
        }

        if (!\is_array($value) || !array_is_list($value)) {
            $this->violation('akawaka_cms.content_block.placements.invalid');

            return;
        }

        if (\count($value) > ValidPlacements::MAX_PLACEMENTS) {
            $this->violation('akawaka_cms.content_block.placements.too_many', ['{{ max }}' => (string) ValidPlacements::MAX_PLACEMENTS]);

            return;
        }

        $seen = [];
        foreach ($value as $placement) {
            $hook = \is_array($placement) ? ($placement['hook'] ?? null) : null;
            $priority = \is_array($placement) ? ($placement['priority'] ?? null) : null;

            if (!\is_string($hook) || !\is_int($priority) || [] !== array_diff(array_keys((array) $placement), ['hook', 'priority'])) {
                $this->violation('akawaka_cms.content_block.placements.invalid');

                return;
            }

            if (!$this->prefixes->allows($hook)) {
                $this->violation('akawaka_cms.content_block.placements.hook_not_allowed', ['{{ hook }}' => $hook, '{{ prefixes }}' => implode(', ', $this->prefixes->all())]);

                return;
            }

            if ($priority < ValidPlacements::MIN_PRIORITY || $priority > ValidPlacements::MAX_PRIORITY) {
                $this->violation('akawaka_cms.content_block.placements.priority_range', ['{{ min }}' => (string) ValidPlacements::MIN_PRIORITY, '{{ max }}' => (string) ValidPlacements::MAX_PRIORITY]);

                return;
            }

            if (isset($seen[$hook])) {
                $this->violation('akawaka_cms.content_block.placements.duplicate', ['{{ hook }}' => $hook]);

                return;
            }

            $seen[$hook] = true;
        }
    }

    /** @param array<string, string> $parameters */
    private function violation(string $message, array $parameters = []): void
    {
        $this->context->buildViolation($message)->setParameters($parameters)->addViolation();
    }
}
