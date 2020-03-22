<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Formatters;

use DateTime;
use Monolog\Formatter\JsonFormatter;

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
