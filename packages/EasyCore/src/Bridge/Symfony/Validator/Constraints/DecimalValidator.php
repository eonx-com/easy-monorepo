<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class DecimalValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if ($constraint instanceof Decimal === false) {
            throw new UnexpectedTypeException($constraint, Decimal::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (\is_scalar($value) === false
            && (\is_object($value) && \method_exists($value, '__toString')) === false) {
            throw new UnexpectedValueException($value, 'scalar');
        }

        $originalValue = $value;
        $value = \abs((float) (string) $value);
        // If checked value is correct we'll get a value with integer part without final zero
        $minPrecisionBound = $value * 10 ** $constraint->minPrecision;
        $lowerThanMinPrecision = $minPrecisionBound > 10 && ((int) $minPrecisionBound) / 10 === 0;
        // If a checked value is correct we'll get an integer value
        // (after fixing possible PHP floating precision error with "round" here)
        $maxPrecisionBound = \round($value * 10 ** $constraint->maxPrecision, 1);
        $moreThanMaxPrecision = ($maxPrecisionBound - (int) $maxPrecisionBound) > 0;

        if ((\is_scalar($originalValue) && \is_numeric($originalValue) === false) ||
            $lowerThanMinPrecision ||
            $moreThanMaxPrecision) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ minPrecision }}', $this->formatValue($constraint->minPrecision))
                ->setParameter('{{ maxPrecision }}', $this->formatValue($constraint->maxPrecision))
                ->setCode(Decimal::INVALID_DECIMAL_ERROR)
                ->addViolation();
        }
    }
}
