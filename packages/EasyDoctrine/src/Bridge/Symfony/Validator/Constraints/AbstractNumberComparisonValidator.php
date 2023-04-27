<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\Symfony\Validator\Constraints;

use EonX\EasyDoctrine\ValueObject\Number as NumberValueObject;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\AbstractComparisonValidator;

abstract class AbstractNumberComparisonValidator extends AbstractComparisonValidator
{
    private AbstractNumberComparison $constraint;

    public function validate(mixed $value, Constraint $constraint): void
    {
        /** @var \EonX\EasyDoctrine\Bridge\Symfony\Validator\Constraints\AbstractNumberComparison $abstractNumberConstraint */
        $abstractNumberConstraint = $constraint;
        $this->constraint = $abstractNumberConstraint;

        parent::validate($value, $constraint);
    }

    /**
     * @param \EonX\EasyDoctrine\ValueObject\Number|int|string $value
     */
    protected function formatValue(mixed $value, ?int $format = null): string
    {
        if ($value instanceof NumberValueObject === false) {
            $value = new NumberValueObject($value);
        }

        return $this->constraint->isMoney ? $value->toMoneyString() : (string)$value;
    }
}
