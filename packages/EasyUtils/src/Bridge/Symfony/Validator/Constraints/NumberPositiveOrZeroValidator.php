<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Bridge\Symfony\Validator\Constraints;

use EonX\EasyUtils\ValueObjects\Number as NumberValueObject;
use Symfony\Component\Validator\Constraints\AbstractComparisonValidator;

final class NumberPositiveOrZeroValidator extends AbstractComparisonValidator
{
    /**
     * @param \EonX\EasyUtils\ValueObjects\Number|null $value1
     * @param \EonX\EasyUtils\ValueObjects\Number|null $value2
     */
    protected function compareValues(mixed $value1, mixed $value2): bool
    {
        return $value1 === null || $value1->isPositiveOrZero();
    }

    /**
     * @param \EonX\EasyUtils\ValueObjects\Number|int|string $value
     */
    protected function formatValue(mixed $value, ?int $format = null): string
    {
        if ($value instanceof NumberValueObject === false) {
            $value = new NumberValueObject($value);
        }

        return (string)$value;
    }
}
