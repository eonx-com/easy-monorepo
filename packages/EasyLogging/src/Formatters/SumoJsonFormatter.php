<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Formatters;

use DateTime;
use Monolog\Formatter\JsonFormatter;

/**
 * @deprecated since 3.10, will be removed in 4.0. Use JsonFormatter instead.
 */
final class SumoJsonFormatter extends JsonFormatter
{
    /**
     * @param mixed[] $record
     */
    public function format(array $record): string
    {
        $record['datetime'] = $record['datetime']->format(DateTime::RFC3339);

        return parent::format($record);
    }
}
