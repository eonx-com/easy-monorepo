<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Math\Validator;

final class NumberGreaterOrEqualValidator extends AbstractNumberComparisonValidator
{
    /**
     * @param \EonX\EasyUtils\Math\ValueObject\Number|null $value1
     * @param \EonX\EasyUtils\Math\ValueObject\Number|null $value2
     */
    protected function compareValues(mixed $value1, mixed $value2): bool
    {
        return $value1 === null || $value2 === null || $value1->isGreaterThanOrEqualTo($value2);
    }
}
