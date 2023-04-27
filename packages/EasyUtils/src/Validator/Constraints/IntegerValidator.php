<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class IntegerValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($constraint instanceof Integer === false) {
            throw new UnexpectedTypeException($constraint, Integer::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (\filter_var($value, \FILTER_VALIDATE_INT) === false) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
