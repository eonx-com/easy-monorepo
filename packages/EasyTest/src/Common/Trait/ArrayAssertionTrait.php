<?php
declare(strict_types=1);

namespace EonX\EasyTest\Common\Trait;

/**
 * @mixin \PHPUnit\Framework\TestCase
 */
trait ArrayAssertionTrait
{
    public static function assertArrayStructure(array $structure, array $array): void
    {
        foreach ($structure as $key => $value) {
            if (\is_array($value)) {
                if ($key === '*') {
                    foreach ($array as $itemKey => $responseDataItem) {
                        static::assertArrayStructure($structure[$key], $responseDataItem);
                        unset($array[$itemKey]);
                    }
                    unset($structure[$key]);
                }
                if ($key !== '*') {
                    static::assertArrayHasKey($key, $array);
                    $isEmptyArray = \count($structure[$key]) === 0;

                    if ($isEmptyArray) {
                        static::assertIsArray($array[$key]);
                        unset($structure[$key]);
                        $structure[] = $key;
                    }

                    if ($isEmptyArray === false) {
                        static::assertArrayStructure($value, $array[$key]);
                        unset($structure[$key], $array[$key]);
                    }
                }
            }
            if (\is_array($value) === false) {
                static::assertArrayHasKey($value, $array);
                static::assertIsNotArray($array[$value]);
            }
        }

        self::assertSame(
            \array_diff(\array_keys($array), $structure),
            \array_diff($structure, \array_keys($array))
        );
    }
}
