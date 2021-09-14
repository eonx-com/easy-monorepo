<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
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
