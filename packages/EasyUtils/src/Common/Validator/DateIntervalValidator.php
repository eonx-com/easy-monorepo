<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Common\Validator;

use DateInterval;
use EonX\EasyUtils\Common\Constraint\DateInterval as DateIntervalConstraint;
use Exception;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use TypeError;

final class DateIntervalValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($constraint instanceof DateIntervalConstraint === false) {
            throw new UnexpectedTypeException($constraint, DateIntervalConstraint::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (\is_string($value) === false) {
            throw new UnexpectedValueException($value, 'string');
        }

        try {
            new DateInterval($value);
        } catch (Exception|TypeError) {
            $this->context->buildViolation($constraint->message)
                ->setCode(DateIntervalConstraint::INVALID_DATE_INTERVAL_ERROR)
                ->addViolation();
        }
    }
}
