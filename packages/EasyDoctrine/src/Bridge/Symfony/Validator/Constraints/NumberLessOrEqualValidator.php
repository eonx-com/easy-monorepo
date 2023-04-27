<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\Symfony\Validator\Constraints;

final class NumberLessOrEqualValidator extends AbstractNumberComparisonValidator
{
    /**
     * @param \EonX\EasyDoctrine\ValueObject\Number|null $value1
     * @param \EonX\EasyDoctrine\ValueObject\Number|null $value2
     */
    protected function compareValues(mixed $value1, mixed $value2): bool
    {
        return $value1 === null || $value2 === null || $value1->isLessThanOrEqualTo($value2);
    }
}
