<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class AlphanumericValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Alphanumeric) {
            throw new UnexpectedTypeException($constraint, Alphanumeric::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (!\is_scalar($value) && !(\is_object($value) && \method_exists($value, '__toString'))) {
            throw new UnexpectedValueException($value, 'string');
        }

        $value = (string) $value;

        if (\preg_match('/^[a-z0-9]+$/i', $value) === 0) {
            $this->context->buildViolation($constraint->message)
                ->setCode(Alphanumeric::INVALID_ALPHANUMERIC_ERROR)
                ->addViolation();
        }
    }
}
