<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
final class Decimal extends Constraint
{
    /**
     * @var string
     */
    public const INVALID_DECIMAL_ERROR = 'INVALID_DECIMAL_ERROR';

    public string $message = 'This value is not a valid decimal or integer number' .
    ', has less than {{ minPrecision }} or more than {{ maxPrecision }} digits in precision.';

    /**
     * @inheritDoc
     */
    public function __construct(
        public int $minPrecision,
        public int $maxPrecision,
        $options = null,
        array $groups = null,
        $payload = null
    ) {
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
}
