<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Bridge\Symfony\Validator\Constraints;

use EonX\EasyUtils\ValueObjects\Number;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class NumberIntegerValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($constraint instanceof NumberInteger === false) {
            throw new UnexpectedTypeException($constraint, NumberInteger::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if ($value instanceof Number === false) {
            throw new UnexpectedTypeException($value, Number::class);
        }

        if (\preg_match('/^[+-]?0*[1-9]*[.]?0*$/', (string)$value) === 0) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
