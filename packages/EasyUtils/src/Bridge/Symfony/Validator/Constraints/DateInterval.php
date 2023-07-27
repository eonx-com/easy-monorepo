<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Bridge\Symfony\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final class DateInterval extends Constraint
{
    public const INVALID_DATE_INTERVAL_ERROR = 'INVALID_DATE_INTERVAL_ERROR';

    public string $message = 'This value is not a valid DateInterval.';
}
