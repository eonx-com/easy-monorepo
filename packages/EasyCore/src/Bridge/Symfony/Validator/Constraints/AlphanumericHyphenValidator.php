<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class AlphanumericHyphenValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($constraint instanceof AlphanumericHyphen === false) {
            throw new UnexpectedTypeException($constraint, AlphanumericHyphen::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (\is_scalar($value) === false
            && (\is_object($value) && \method_exists($value, '__toString')) === false) {
            throw new UnexpectedValueException($value, 'string');
        }

        $value = (string)$value;

        if (\preg_match('/^[a-z0-9_-]+$/i', $value) === 0) {
            $this->context->buildViolation($constraint->message)
                ->setCode(AlphanumericHyphen::INVALID_ALPHANUMERIC_HYPHEN_ERROR)
                ->addViolation();
        }
    }
}
