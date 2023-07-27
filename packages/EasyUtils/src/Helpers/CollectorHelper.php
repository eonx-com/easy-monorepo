<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Helpers;

use EonX\EasyUtils\Exceptions\InvalidArgumentException;
use EonX\EasyUtils\Interfaces\HasPriorityInterface;
use Traversable;

final class CollectorHelper
{
    public static function convertToArray(iterable $items): array
    {
        return $items instanceof Traversable ? \iterator_to_array($items) : $items;
    }

    /**
     * @throws \EonX\EasyUtils\Exceptions\InvalidArgumentException
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
     * @throws \EonX\EasyUtils\Exceptions\InvalidArgumentException
     */
    public static function ensureClassAsArray(iterable $items, string $class): array
    {
        return self::convertToArray(self::ensureClass($items, $class));
    }

    public static function filterByClass(iterable $items, string $class): iterable
    {
        foreach ($items as $item) {
            if ($item instanceof $class) {
                yield $item;
            }
        }
    }

    /**
     * @param class-string $class
     */
    public static function filterByClassAsArray(iterable $items, string $class): array
    {
        return self::convertToArray(self::filterByClass($items, $class));
    }

    public static function orderHigherPriorityFirst(iterable $items): iterable
    {
        $items = self::convertToArray($items);

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

    public static function orderHigherPriorityFirstAsArray(iterable $items): array
    {
        return self::convertToArray(self::orderHigherPriorityFirst($items));
    }

    public static function orderLowerPriorityFirst(iterable $items): iterable
    {
        $items = self::convertToArray($items);

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

    public static function orderLowerPriorityFirstAsArray(iterable $items): array
    {
        return self::convertToArray(self::orderLowerPriorityFirst($items));
    }
}
