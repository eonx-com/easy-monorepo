<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Formatters;

use Monolog\Formatter\JsonFormatter as BaseJsonFormatter;

final class JsonFormatter extends BaseJsonFormatter
{
    /**
     * @param mixed $data
     * @param null|int $depth
     *
     * @return mixed
     */
    protected function normalize($data, $depth = null)
    {
        return parent::normalize($this->formatDateTimes($data), $depth ?? 0);
    }

    /**
     * @param mixed $data
     *
     * @return mixed
     */
    private function formatDateTimes($data)
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
