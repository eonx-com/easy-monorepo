---eonx_docs---
title: Introduction
weight: 0
---eonx_docs---

# EasyUtils

This package provides helper classes.

## Require package (Composer)

The recommended way to install this package is to use [Composer][1]:

```bash
$ composer require eonx-com/easy-utils
```

## List of helpers

- `CollectorHelper`: provide methods to facilitate the implementation of Collector DesignPattern

## CollectorHelper

The [Collector DesignPattern][2] is a great solution to contribute keeping your code SOLID, however when you start
implementing it in different places of your project you end up repeating yourself a bit.
The main purpose of this helper is to prevent duplicated code and facilitate the implementation of the [Collector DesignPattern][2]
in your project.

### CollectorHelper::convertToArray()

The `convertToArray()` method is pretty straight forward, it will convert any iterable to a simple PHP array. It comes
handy when you deal with `iterable` and you want to use array methods on it.

Here is a simple example of when to use this method, you have a class which accepts an `iterable` of "workers"
in its constructor. To safely use those "workers", you want to ensure each of them implements the right interface, so
you decide to filter them to keep only the "good workers" using the `array_filter()` function. If the given "workers"
are already an `array`, then no problem, however it is defined as `iterable` so you cannot guarantee you will receive
an `array`. Use the `convertToArray()` method!

```php
use App\Domain\WorkerInterface;
use EonX\EasyUtils\CollectorHelper;

final class MyClass
{
    /**
     * @var \App\Domain\WorkerInterface[]
     */
    private array $workers;

    /**
     * @param iterable<mixed> $workers
     */
    public function __construct(iterable $workers)
    {
        // $workers could be any type of iterable, convert it to array
        $workers = CollectorHelper::convertToArray($workers);

        // Now we are sure $workers is an array, we can use array_filter()
        $workers = \array_filter($workers, static function ($worker): bool {
            return $worker instanceof WorkerInterface;
        });

        // $workers is now an array of WorkerInterface for sure
        $this->workers = $workers;
    }
}
```

### CollectorHelper::filterByClass()

The use case used above to explain the `convertToArray()` method is really common (at least in our projects :smiley: ),
this is why this helper comes with a method to do it for you.

The example is exactly the same as for the last method, you have an `iterable` and you want to make sure each item is
an instance of a specific class/interface, use `filterByClass()`!

```php
use App\Domain\WorkerInterface;
use EonX\EasyUtils\CollectorHelper;

final class MyClass
{
    /**
     * @var iterable<\App\Domain\WorkerInterface>
     */
    private array $workers;

    /**
     * @param iterable<mixed> $workers
     */
    public function __construct(iterable $workers)
    {
        // $workers now contains only WorkerInterface instances
        $workers = CollectorHelper::filterByClass(WorkerInterface::class, $workers);

        // The filterByClass() method still returns an iterable, a generator more precisely
        // If you need an array, you can use the filterByClassAsArray() method
        $this->workers = $workers;
    }
}
```

::: tip
The `filterByClass()` method still returns an iterable, a generator more precisely.
If you need an `array`, you can use the `filterByClassAsArray()` method.
:::

### CollectorHelper::filterByClassAsArray()

Same use case as the previous methods with a little tweak, you have an `iterable`, you want to make sure each item is
an instance of a specific class/interface, but you need the output to be an `array`, use `filterByClassAsArray()`!

```php
use App\Domain\WorkerInterface;
use EonX\EasyUtils\CollectorHelper;

final class MyClass
{
    /**
     * @var \App\Domain\WorkerInterface[]
     */
    private array $workers;

    /**
     * @param iterable<mixed> $workers
     */
    public function __construct(iterable $workers)
    {
        // $workers now contains only WorkerInterface instances
        $workers = CollectorHelper::filterByClassAsArray(WorkerInterface::class, $workers);

        // $workers is now an array containing only WorkerInterface instances
        $this->workers = $workers;
    }
}
```

[1]: https://getcomposer.org/
[2]: https://tomasvotruba.com/blog/2018/06/14/collector-pattern-for-dummies/
