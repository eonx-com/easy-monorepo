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
final class Alphanumeric extends AbstractConstraint
{
    public const INVALID_ALPHANUMERIC_ERROR = 'INVALID_ALPHANUMERIC_ERROR';

    public string $message = 'This value may only contain letters and numbers.';
}
