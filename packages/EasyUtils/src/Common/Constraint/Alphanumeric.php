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
    public const string INVALID_ALPHANUMERIC_ERROR = 'INVALID_ALPHANUMERIC_ERROR';

    public string $message = 'alphanumeric.not_valid';
}
