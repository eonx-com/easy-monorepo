<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
final class Integer extends Constraint
{
    /**
     * @var string
     */
    public const INVALID_INTEGER_ERROR = 'INVALID_INTEGER_ERROR';

    public string $message = 'This value should be of type integer.';
}
