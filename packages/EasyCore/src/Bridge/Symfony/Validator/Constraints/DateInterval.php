<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Attribute;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_METHOD)]
final class DateInterval extends Constraint
{
    /**
     * @var string
     */
    public const INVALID_DATE_INTERVAL_ERROR = 'INVALID_DATE_INTERVAL_ERROR';

    /**
     * @var string
     */
    public $message = 'This value is not a valid DateInterval.';
}
