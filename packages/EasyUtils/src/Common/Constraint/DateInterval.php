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
final class DateInterval extends AbstractConstraint
{
    public const string INVALID_DATE_INTERVAL_ERROR = 'INVALID_DATE_INTERVAL_ERROR';

    public string $message = 'date_interval.not_valid';
}
