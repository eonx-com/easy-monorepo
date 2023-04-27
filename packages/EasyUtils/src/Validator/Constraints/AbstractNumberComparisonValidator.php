<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Validator\Constraints;

use EonX\EasyUtils\ValueObject\Number as NumberValueObject;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\AbstractComparisonValidator;

abstract class AbstractNumberComparisonValidator extends AbstractComparisonValidator
{
    private AbstractNumberComparison $constraint;

    public function validate(mixed $value, Constraint $constraint): void
    {
        /** @var \EonX\EasyUtils\Validator\Constraints\AbstractNumberComparison $abstractNumberConstraint */
        $abstractNumberConstraint = $constraint;
        $this->constraint = $abstractNumberConstraint;

        parent::validate($value, $constraint);
    }

    /**
     * @param \EonX\EasyUtils\ValueObject\Number|int|string $value
     */
    protected function formatValue(mixed $value, ?int $format = null): string
    {
        if ($value instanceof NumberValueObject === false) {
            $value = new NumberValueObject($value);
        }

        return $this->constraint->isMoney ? $value->toMoneyString() : (string)$value;
    }
}
