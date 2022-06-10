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

        if (\is_object($value)) {
            $value = (string) $value;
        }

        // get real string representation of float, type cast "(string)" cut precision, var_dump not
        if (\is_float($value)) {
            \ob_start();
            \var_dump($value);
            \preg_match('/float\((?P<float>[^\)]+)\)/', \ob_get_clean() ?: '', $matches);
            $value = $matches['float'];
        }

        $value = (string) $value;

        // fix epsilon representation using sprintf
        if (\str_contains($value, 'E') === true) {
            \preg_match('/E\-(?P<precision>\d+)/', $value, $matches);
            $value = \sprintf('%.' . ($matches['precision'] ?? 0) . 'f', $value);
        }

        // fix int value if required precision
        if ($constraint->minPrecision > 1 && \str_contains($value, '.') === false) {
            $value .= '.0';
        }

        $pattern = \sprintf('/^\-?\d+(\.\d{%d,%d}0*)?$/', $constraint->minPrecision, $constraint->maxPrecision);

        if (\preg_match($pattern, $value) === 0) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ minPrecision }}', $this->formatValue($constraint->minPrecision))
                ->setParameter('{{ maxPrecision }}', $this->formatValue($constraint->maxPrecision))
                ->setCode(Decimal::INVALID_DECIMAL_ERROR)
                ->addViolation();
        }
    }
}
