<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Common\Constraint;

use Attribute;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * @Annotation
 *
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final class Decimal extends AbstractConstraint
{
    public const string INVALID_DECIMAL_ERROR = 'INVALID_DECIMAL_ERROR';

    public int $maxPrecision;

    public string $message = 'decimal.not_valid';

    public int $minPrecision;

    public function __construct(
        mixed $options = null,
        ?int $minPrecision = null,
        ?int $maxPrecision = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        if ($minPrecision === null) {
            $minPrecision = 0;

            if (\is_array($options) && isset($options['minPrecision'])) {
                $minPrecision = \intval($options['minPrecision']);
            }
        }

        if ($maxPrecision === null) {
            $maxPrecision = 0;

            if (\is_array($options) && isset($options['maxPrecision'])) {
                $maxPrecision = \intval($options['maxPrecision']);
            }
        }

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
