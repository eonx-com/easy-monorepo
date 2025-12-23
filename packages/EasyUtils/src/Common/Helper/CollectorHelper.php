<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Common\Helper;

use EonX\EasyUtils\Common\Exception\InvalidArgumentException;

final class CollectorHelper
{
    /**
     * @template TKey
     * @template TValue
     *
     * @param iterable<TKey, TValue> $items
     *
     * @return array<TKey, TValue>|TValue[]
     *
     * @deprecated Will be removed in 7.0. Use `iterator_to_array` directly instead.
     */
    public static function convertToArray(iterable $items): array
    {
        return \iterator_to_array($items);
    }

    /**
     * @template TValue of object
     *
     * @param class-string<TValue> $class
     *
     * @return iterable<TValue>
     *
     * @throws \EonX\EasyUtils\Common\Exception\InvalidArgumentException
     */
    public static function ensureClass(iterable $items, string $class): iterable
    {
        foreach ($items as $item) {
            if ($item instanceof $class === false) {
                throw new InvalidArgumentException(\sprintf(
                    'Instance of %s expected, %s given',
                    $class,
                    \get_debug_type($item)
                ));
            }

            yield $item;
        }
    }

    /**
     * @template TValue of object
     *
     * @param class-string<TValue> $class
     *
     * @return list<TValue>
     *
     * @throws \EonX\EasyUtils\Common\Exception\InvalidArgumentException
     */
    public static function ensureClassAsArray(iterable $items, string $class): array
    {
        return \iterator_to_array(self::ensureClass($items, $class));
    }

    /**
     * @template TValue of object
     *
     * @param class-string<TValue> $class
     *
     * @return iterable<TValue>
     */
    public static function filterByClass(iterable $items, string $class): iterable
    {
        foreach ($items as $item) {
            if ($item instanceof $class) {
                yield $item;
            }
        }
    }

    /**
     * @template TValue of object
     *
     * @param class-string<TValue> $class
     *
     * @return list<TValue>
     */
    public static function filterByClassAsArray(iterable $items, string $class): array
    {
        return \iterator_to_array(self::filterByClass($items, $class));
    }

    /**
     * @template TValue of object
     *
     * @param iterable<TValue> $items
     *
     * @return iterable<TValue>
     */
    public static function orderHigherPriorityFirst(iterable $items): iterable
    {
        $items = \iterator_to_array($items);

        \usort($items, static function ($first, $second): int {
            $firstPriority = $first instanceof HasPriorityInterface ?
                $first->getPriority() :
                HasPriorityInterface::DEFAULT_PRIORITY;
            $secondPriority = $second instanceof HasPriorityInterface ?
                $second->getPriority() :
                HasPriorityInterface::DEFAULT_PRIORITY;

            return $secondPriority <=> $firstPriority;
        });

        foreach ($items as $item) {
            yield $item;
        }
    }

    /**
     * @template TValue of object
     *
     * @param iterable<TValue> $items
     *
     * @return list<TValue>
     */
    public static function orderHigherPriorityFirstAsArray(iterable $items): array
    {
        return \iterator_to_array(self::orderHigherPriorityFirst($items));
    }

    /**
     * @template TValue of object
     *
     * @param iterable<TValue> $items
     *
     * @return iterable<TValue>
     */
    public static function orderLowerPriorityFirst(iterable $items): iterable
    {
        $items = \iterator_to_array($items);

        \usort($items, static function ($first, $second): int {
            $firstPriority = $first instanceof HasPriorityInterface ?
                $first->getPriority() :
                HasPriorityInterface::DEFAULT_PRIORITY;
            $secondPriority = $second instanceof HasPriorityInterface ?
                $second->getPriority() :
                HasPriorityInterface::DEFAULT_PRIORITY;

            return $firstPriority <=> $secondPriority;
        });

        foreach ($items as $item) {
            yield $item;
        }
    }

    /**
     * @template TValue of object
     *
     * @param iterable<TValue> $items
     *
     * @return list<TValue>
     */
    public static function orderLowerPriorityFirstAsArray(iterable $items): array
    {
        return \iterator_to_array(self::orderLowerPriorityFirst($items));
    }
}
