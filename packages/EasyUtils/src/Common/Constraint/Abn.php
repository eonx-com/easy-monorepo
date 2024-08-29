<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Common\Constraint;

use Attribute;

/**
 * @Annotation
 *
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final class Abn extends AbstractConstraint
{
    public const INVALID_CHARACTERS_ERROR = 'INVALID_CHARACTERS_ERROR';

    public const INVALID_LENGTH_ERROR = 'INVALID_LENGTH_ERROR';

    public const LEADING_ZERO_ERROR = 'LEADING_ZERO_ERROR';

    public const MODULUS_CALCULATION_FAILED_ERROR = 'MODULUS_CALCULATION_FAILED_ERROR';

    public string $message = 'This field must be an 11-digit string representing a valid Australian Business Number.';

    public function __construct(?array $groups = null, ?string $message = null)
    {
        $this->message = $message ?? $this->message;

        parent::__construct(groups: $groups);
    }
}
