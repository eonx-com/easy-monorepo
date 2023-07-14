<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Formatters;

use Monolog\Formatter\JsonFormatter as BaseJsonFormatter;

final class JsonFormatter extends BaseJsonFormatter
{
    /**
     * @param null|int $depth
     */
    protected function normalize(mixed $data, mixed $depth = null): mixed
    {
        return parent::normalize($this->formatDateTimes($data), $depth ?? 0);
    }

    private function formatDateTimes(mixed $data): mixed
    {
        if (\is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->formatDateTimes($value);
            }
        }

        if ($data instanceof \DateTimeInterface) {
            return $data->format('Y-m-d\TH:i:sP');
        }

        return $data;
    }
}
