<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Helpers;

final class ArrayHelper
{
    /**
     * @param mixed[] $array
     *
     * @return mixed[]
     */
    public static function flatten(array $array, ?string $prepend = null): array
    {
        $flattened = [];

        foreach ($array as $key => $value) {
            // If value is an array, recurse
            if (\is_array($value) && \count($value)) {
                $flattened[] = self::flatten($value, \sprintf('%s%s/', $prepend, $key));

                continue;
            }

            // Set value
            $flattened[] = [
                \sprintf('%s%s', $prepend, $key) => $value,
            ];
        }

        // Merge flattened keys if some were found otherwise return an empty array
        return \count($flattened) ? \array_merge(...$flattened) : [];
    }

    /**
     * @param mixed[] $array
     */
    public static function set(array &$array, mixed $key, mixed $value): void
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
     * @param mixed[] ...$replacements
     *
     * @return mixed[]
     */
    public static function smartReplace(array $array, array ...$replacements): array
    {
        $flattenArray = self::flatten($array);

        foreach ($replacements as $replacement) {
            $flattenArray = \array_merge($flattenArray, self::flatten($replacement));
        }

        return self::unflatten($flattenArray);
    }

    /**
     * @param mixed[] $array
     *
     * @return mixed[]
     */
    public static function unflatten(array $array): array
    {
        $unpacked = [];

        // The set() method recurses the array and unflattens dot notations correctly, so just pass-through
        foreach ($array as $key => $value) {
            self::set($unpacked, (string)$key, $value);
        }

        return $unpacked;
    }
}
