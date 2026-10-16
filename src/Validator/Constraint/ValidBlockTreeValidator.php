<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Validator\Constraint;

use Akawaka\SyliusCMSPlugin\Validator\BlockTreeValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class ValidBlockTreeValidator extends ConstraintValidator
{
    public function __construct(private readonly BlockTreeValidator $treeValidator)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidBlockTree) {
            throw new UnexpectedTypeException($constraint, ValidBlockTree::class);
        }

        foreach ($this->treeValidator->validate($value ?? []) as $violation) {
            $this->context
                ->buildViolation($violation->message)
                ->setParameters(['{{ path }}' => $violation->path] + $violation->parameters)
                ->addViolation()
            ;
        }
    }
}
