<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Helpers;

use EonX\EasyUtils\Exceptions\InvalidArgumentException;
use EonX\EasyUtils\Interfaces\HasPriorityInterface;

class CollectorHelper
{
    /**
     * @param iterable<mixed> $items
     *
     * @return mixed[]
     */
    public static function convertToArray(iterable $items): array
    {
        return $items instanceof \Traversable ? \iterator_to_array($items) : $items;
    }

    /**
     * @param iterable<mixed> $items
     *
     * @return iterable<mixed>
     *
     * @throws \EonX\EasyUtils\Exceptions\InvalidArgumentException
     */
    public static function ensureClass(iterable $items, string $class): iterable
    {
        foreach ($items as $item) {
            if (($item instanceof $class) === false) {
                throw new InvalidArgumentException(\sprintf(
                    'Instance of %s expected, %s given',
                    $class,
                    \is_object($item) === false ? \gettype($item) : \get_class($item),
                ));
            }

            yield $item;
        }
    }

    /**
     * @param iterable<mixed> $items
     *
     * @return mixed[]
     *
     * @throws \EonX\EasyUtils\Exceptions\InvalidArgumentException
     */
    public static function ensureClassAsArray(iterable $items, string $class): array
    {
        return self::convertToArray(self::ensureClass($items, $class));
    }

    /**
     * @param iterable<mixed> $items
     *
     * @return iterable<mixed>
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
     * @param iterable<mixed> $items
     * @param class-string $class
     *
     * @return mixed[]
     */
    public static function filterByClassAsArray(iterable $items, string $class): array
    {
        return self::convertToArray(self::filterByClass($items, $class));
    }

    /**
     * @param iterable<mixed> $items
     *
     * @return iterable<mixed>
     */
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

    /**
     * @param iterable<mixed> $items
     *
     * @return mixed[]
     */
    public static function orderHigherPriorityFirstAsArray(iterable $items): array
    {
        return self::convertToArray(self::orderHigherPriorityFirst($items));
    }

    /**
     * @param iterable<mixed> $items
     *
     * @return iterable<mixed>
     */
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

    /**
     * @param iterable<mixed> $items
     *
     * @return mixed[]
     */
    public static function orderLowerPriorityFirstAsArray(iterable $items): array
    {
        return self::convertToArray(self::orderLowerPriorityFirst($items));
    }
}
