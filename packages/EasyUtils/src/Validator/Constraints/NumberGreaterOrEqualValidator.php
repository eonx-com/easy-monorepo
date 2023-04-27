<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Validator\Constraints;

final class NumberGreaterOrEqualValidator extends AbstractNumberComparisonValidator
{
    /**
     * @param \EonX\EasyUtils\ValueObject\Number|null $value1
     * @param \EonX\EasyUtils\ValueObject\Number|null $value2
     */
    protected function compareValues(mixed $value1, mixed $value2): bool
    {
        return $value1 === null || $value2 === null || $value1->isGreaterThanOrEqualTo($value2);
    }
}
