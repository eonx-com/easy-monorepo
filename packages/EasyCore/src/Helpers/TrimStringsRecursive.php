<?php

declare(strict_types=1);

namespace EonX\EasyCore\Helpers;

use EonX\EasyCore\Tests\Helpers\CleanerInterface;

final class TrimStringsRecursive implements CleanerInterface
{
    /**
     * @var mixed[]
     */
    private $except;

    public function clean($data, array $except = [])
    {
        $this->except = $except;

        if (\is_array($data)) {
            return $this->cleanArray($data, '');
        }

        return $this->transform($data, '');
    }

    /**
     * Clean the data in the given array.
     *
     * @return mixed[]
     */
    private function cleanArray(array $data, string $keyPrefix = ''): array
    {
        foreach ($data as $key => $value) {
            if (\is_array($value) === false) {
                $data[$key] = $this->transform($value, $keyPrefix . $key);
            } elseif (\in_array($keyPrefix . $key, $this->except, true) === false) {
                $data[$key] = $this->cleanArray($value, $keyPrefix . $key . '.');
            }
        }

        return $data;
    }

    /**
     * Transform the given value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    private function transform($value, string $key)
    {
        if (\in_array($key, $this->except, true)) {
            return $value;
        }

        return \is_string($value) ? \trim($value) : $value;
    }
}
