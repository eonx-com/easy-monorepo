<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Bridge\Symfony\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final class Decimal extends Constraint
{
    public const INVALID_DECIMAL_ERROR = 'INVALID_DECIMAL_ERROR';

    public int $maxPrecision;

    public string $message = 'This value is not a valid decimal or integer number' .
    ', has less than {{ minPrecision }} or more than {{ maxPrecision }} digits in precision.';

    public int $minPrecision;

    public function __construct(
        $options = null,
        int $minPrecision = null,
        int $maxPrecision = null,
        array $groups = null,
        $payload = null,
    ) {
        $minPrecision = (int)($minPrecision ?? $options['minPrecision'] ?? 0);
        $maxPrecision = (int)($maxPrecision ?? $options['maxPrecision'] ?? 0);

        if ($minPrecision < 1) {
            throw new ConstraintDefinitionException('The "minPrecision" option must be an integer greater than zero.');
        }

        if ($maxPrecision < $minPrecision) {
            throw new ConstraintDefinitionException(
                'The "maxPrecision" option must be an integer greater than "minPrecision".'
            );
        }

        parent::__construct($options, $groups, $payload);
    }

    public function getRequiredOptions(): array
    {
        return ['minPrecision', 'maxPrecision'];
    }
}
