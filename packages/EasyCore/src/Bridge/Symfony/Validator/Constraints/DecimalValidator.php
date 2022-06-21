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

        $value = (string) $value;

        if (\is_numeric($value) === true && \str_contains($value, 'E') === true) {
            $value = \sprintf('%0.10f', $value);
        }

        if ($value !== '0') {
            $value = \rtrim(\rtrim($value, '0'), '.');
        }

        $pattern = \sprintf('/^\-?\d+(\.\d{%d,%d})?$/', $constraint->minPrecision, $constraint->maxPrecision);

        if (\preg_match($pattern, $value) === 0) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ minPrecision }}', $this->formatValue($constraint->minPrecision))
                ->setParameter('{{ maxPrecision }}', $this->formatValue($constraint->maxPrecision))
                ->setCode(Decimal::INVALID_DECIMAL_ERROR)
                ->addViolation();
        }
    }
}
