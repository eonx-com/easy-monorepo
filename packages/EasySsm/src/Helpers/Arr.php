<?php

declare(strict_types=1);

namespace EonX\EasySsm\Helpers;

final class Arr
{
    /**
     * @param mixed[] $array
     *
     * @return mixed[]
     */
    public function flatten(array $array, ?string $prepend = null): array
    {
        $flattened = [];

        foreach ($array as $key => $value) {
            // If value is an array, recurse
            if (\is_array($value) && \count($value)) {
                $flattened[] = $this->flatten($value, \sprintf('%s%s/', (string)$prepend, $key));

                continue;
            }

            // Set value
            $flattened[] = [\sprintf('%s%s', (string)$prepend, $key) => $value];
        }

        // Merge flattened keys if some were found otherwise return an empty array
        return \count($flattened) ? \array_merge(...$flattened) : [];
    }

    /**
     * @param mixed[] $array
     * @param mixed $key
     * @param mixed $value
     */
    public function set(array &$array, $key, $value): void
    {
        $keys = \explode('/', (string)$key);

        // Iterate through key parts to find the position to set the value
        while (\count($keys) > 1) {
            $key = \array_shift($keys);

            if (empty($key)) {
                continue;
            }

            if (isset($array[$key]) === false || \is_array($array[$key]) === false) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        // Set value
        $array[\array_shift($keys)] = $value;
    }

    /**
     * @param mixed[] $array
     *
     * @return mixed[]
     */
    public function unflatten(array $array): array
    {
        $unpacked = [];

        // set() recurses the array and unflattens dot notations correctly, so just pass-through
        foreach ($array as $key => $value) {
            $this->set($unpacked, (string)$key, $value);
        }

        return $unpacked;
    }
}
