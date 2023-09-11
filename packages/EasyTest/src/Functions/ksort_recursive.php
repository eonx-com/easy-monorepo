<?php
declare(strict_types=1);

if (\function_exists('ksort_recursive') === false) {
    function ksort_recursive(array &$array, int $flags = SORT_REGULAR): void
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                ksort_recursive($value, $flags);
            }
        }

        ksort($array, $flags);
    }
}
