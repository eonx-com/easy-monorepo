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
final class AlphanumericHyphen extends AbstractConstraint
{
    public const INVALID_ALPHANUMERIC_HYPHEN_ERROR = 'INVALID_ALPHANUMERIC_HYPHEN_ERROR';

    public string $message = 'alphanumeric_hyphen.not_valid';
}
