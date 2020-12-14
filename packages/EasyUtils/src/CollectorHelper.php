<?php

declare(strict_types=1);

namespace EonX\EasyUtils;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;

final class CollectorHelper
{
    /**
     * @param iterable<mixed> $items
     *
     * @return mixed[]
     */
    public static function convertToArray(iterable $items): array
    {
        return $items instanceof \Traversable ? \iterator_to_array($items) : (array)$items;
    }

    /**
     * @param iterable<mixed> $items
     *
     * @return mixed[]
     */
    public static function filterByClass(iterable $items, string $class): array
    {
        $return = [];

        foreach ($items as $item) {
            if ($item instanceof $class) {
                $return[] = $item;
            }
        }

        return $return;
    }

    /**
     * @param mixed[] $items
     *
     * @return mixed[]
     */
    public static function orderHigherPriorityFirst(iterable $items): array
    {
        $items = self::convertToArray($items);

        \usort($items, static function ($first, $second): int {
            $firstPriority = $first instanceof HasPriorityInterface ? $first->getPriority() : HasPriorityInterface::DEFAULT_PRIORITY;
            $secondPriority = $second instanceof HasPriorityInterface ? $second->getPriority() : HasPriorityInterface::DEFAULT_PRIORITY;

            return $secondPriority <=> $firstPriority;
        });

        return $items;
    }

    /**
     * @param mixed[] $items
     *
     * @return mixed[]
     */
    public static function orderLowerPriorityFirst(iterable $items): array
    {
        $items = self::convertToArray($items);

        \usort($items, static function ($first, $second): int {
            $firstPriority = $first instanceof HasPriorityInterface ? $first->getPriority() : HasPriorityInterface::DEFAULT_PRIORITY;
            $secondPriority = $second instanceof HasPriorityInterface ? $second->getPriority() : HasPriorityInterface::DEFAULT_PRIORITY;

            return $firstPriority <=> $secondPriority;
        });

        return $items;
    }
}
