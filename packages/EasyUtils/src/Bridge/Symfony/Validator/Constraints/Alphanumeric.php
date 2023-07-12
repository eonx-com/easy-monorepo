<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
final class Alphanumeric extends Constraint
{
    /**
     * @var string
     */
    public const INVALID_ALPHANUMERIC_ERROR = 'INVALID_ALPHANUMERIC_ERROR';

    /**
     * @var string
     */
    public $message = 'This value may only contain letters and numbers.';
}
