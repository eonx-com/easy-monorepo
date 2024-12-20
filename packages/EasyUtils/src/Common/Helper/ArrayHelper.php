<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Common\Helper;

final class ArrayHelper
{
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

    public static function set(array &$array, int|string $key, mixed $value): void
    {
        $keys = \explode('/', (string)$key);

        // Iterate through key parts to find the position to set the value
        while (\count($keys) > 1) {
            $firstKey = \array_shift($keys);

            if ($firstKey === '') {
                continue;
            }

            if (isset($array[$firstKey]) === false || \is_array($array[$firstKey]) === false) {
                $array[$firstKey] = [];
            }

            $array = &$array[$firstKey];
        }

        // Set value
        $array[\array_shift($keys)] = $value;
    }

    public static function smartReplace(array $array, array ...$replacements): array
    {
        $flattenArray = self::flatten($array);

        foreach ($replacements as $replacement) {
            $flattenArray = \array_merge($flattenArray, self::flatten($replacement));
        }

        return self::unflatten($flattenArray);
    }

    public static function unflatten(array $array): array
    {
        $unpacked = [];

        // The set() method recurses the array and unflattens dot notations correctly, so just pass-through
        foreach ($array as $key => $value) {
            self::set($unpacked, $key, $value);
        }

        return $unpacked;
    }
}
