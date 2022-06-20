<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Validator\Constraints;

use EonX\EasyCore\Bridge\Symfony\Validator\Constraints\Integer as IntegerConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class IntegerValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($constraint instanceof IntegerConstraint === false) {
            throw new UnexpectedTypeException($constraint, IntegerConstraint::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (\filter_var($value, \FILTER_VALIDATE_INT) === false) {
            $this->context->buildViolation($constraint->message)
                ->setCode(IntegerConstraint::INVALID_INTEGER_ERROR)
                ->addViolation();
        }
    }
}
