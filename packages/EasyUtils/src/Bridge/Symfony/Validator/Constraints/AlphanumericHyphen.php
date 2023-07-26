<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Bridge\Symfony\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final class AlphanumericHyphen extends Constraint
{
    /**
     * @var string
     */
    public const INVALID_ALPHANUMERIC_HYPHEN_ERROR = 'INVALID_ALPHANUMERIC_HYPHEN_ERROR';

    public string $message = 'This value may only contain letters, numbers, and hyphens.';
}
