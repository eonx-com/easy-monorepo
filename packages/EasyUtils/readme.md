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

## Helper list

- `CollectorHelper`: provides methods to facilitate implementation of the [Collector Design Pattern][2]
- `Math`: provides methods to facilitate numbers manipulation
- `SensitiveDataSanitizer`: helps you sanitize sensitive data

## CollectorHelper

The [Collector Design Pattern][2] is a great method for keeping your code SOLID. However, using it in multiple parts of
your project can lead to significant repetition. The main purpose of the `CollectorHelper` is to prevent duplicated code
and facilitate implementation of the [Collector Design Pattern][2] in your project.

Most popular PHP frameworks provide features to tag services, and then define all services for a specific tag as
dependencies to other services. See the following resources for examples:

- [Tagging Services in CakePHP][3]
- [How to Work with Service Tags in Symfony][4]
- [Service Tagging in Laravel][5]

Those features help you implement the [Collector Design Pattern][2] in your project because they allow you to easily
inject a collection of services sharing the same tag into other services.

However, there are some things you need to consider:

- There is no guarantee that all given services are instances of a specific class/interface
- You have no control on the order the services are organised within the given collection

Let's elaborate on the above points.

#### No guarantee on the content of tagged services

The service tagging features do not allow you to ensure all services sharing the same tag meet common criteria. Symfony
has a feature to [automatically tag services based on their class][6], but nothing stops you from manually tagging a
service with the same tag or even one of your dependencies.

This is why we strongly recommend you always filter the given `iterable` of services by a given class/interface of your
choice by using the `filterByClass()` or `filterByClassAsArray()` methods of the `CollectorHelper`.

#### No control on the order the services are organised

When using service tagging features, you can control the order that the services are organised by simply changing the
order in which you define the services. However, as above, there is nothing stopping you or one of your dependencies
from tagging a service with the same tag. Therefore, you cannot guarantee the order as you cannot modify the
dependencies' service definitions. But the `CollectorHelper` can help us!

In some cases, the order of the given services does not matter, so there is no need to do anything. But if your logic
requires the services be used in a specific order, then use the `orderHigherPriorityFirst()` and/or
`orderLowerPriorityFirst()` methods!

These methods will sort the objects within the given `iterable` based on their priority. In order to define an object's
priority, it must implement the `EonX\EasyUtils\Interfaces\HasPriorityInterface` provided by this package. If an object
doesn't implement this interface then its priority will default to `0` automatically.

### CollectorHelper::convertToArray()

The `convertToArray()` method will convert any iterable to a simple PHP array. It is useful when you want to use array
methods on an `iterable`.

For a simple example of when to use the `convertToArray()` method, imagine you have a class which accepts an `iterable`
of "workers" in its constructor. To safely use these "workers", you want to ensure each of them implements the right
interface, so you filter them to keep only the "good workers" by using the `array_filter()` function. If the "workers"
were already an `array`, then there would be no problem. However, because they are defined as `iterable`, you cannot
guarantee you will receive an `array`. So use the `convertToArray()` method!

```php
use App\Domain\WorkerInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;

final class MyClass
{
    /**
     * @var \App\Domain\WorkerInterface[]
     */
    private array $workers;

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

The use case of filtering by class (used above to explain the `convertToArray()` method) is very common (at least in our
projects :smiley: ), which is why `CollectorHelper` provides the `filterByClass()` method to do it for you.

The following example is the same as for the `convertToArray()` method above. If you have an `iterable` and you want to
ensure each item is an instance of a specific class/interface, use the `filterByClass()` method!

```php
use App\Domain\WorkerInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;

final class MyClass
{
    /**
     * @var iterable<\App\Domain\WorkerInterface>
     */
    private array $workers;

    public function __construct(iterable $workers)
    {
        // $workers now contains only WorkerInterface instances
        $workers = CollectorHelper::filterByClass($workers, WorkerInterface::class);

        // The filterByClass() method still returns an iterable, a generator more precisely
        // If you need an array, you can use the filterByClassAsArray() method
        $this->workers = $workers;
    }
}
```

::: tip
The `filterByClass()` method still returns an iterable (or, more precisely, a generator). If you need an `array`, you
can use the `filterByClassAsArray()` method instead.
:::

### CollectorHelper::filterByClassAsArray()

This method is similar to the `filterByClass()` method, but with a little tweak. If you have an `iterable` and you want
to make sure each item is an instance of a specific class/interface, but you need the output to be an `array`, use the
`filterByClassAsArray()` method!

```php
use App\Domain\WorkerInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;

final class MyClass
{
    /**
     * @var \App\Domain\WorkerInterface[]
     */
    private array $workers;

    public function __construct(iterable $workers)
    {
        // $workers now contains only WorkerInterface instances
        $workers = CollectorHelper::filterByClassAsArray($workers, WorkerInterface::class);

        // $workers is now an array containing only WorkerInterface instances
        $this->workers = $workers;
    }
}
```

### CollectorHelper::ensureClass() and CollectorHelper::ensureClassAsArray()

Those methods are similar to the `filterByClass()` and `filterByClassAsArray()` methods, however they will throw an
exception if at least of the items is not an instance of the given class.

```php
use App\Domain\WorkerInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;

final class MyClass
{
    /**
     * @var \App\Domain\WorkerInterface[]
     */
    private array $workers;

    public function __construct(iterable $workers)
    {
        // $workers now contains only WorkerInterface instances
        $workers = CollectorHelper::ensureClass(WorkerInterface::class, $workers);

        foreach ($workers as $worker) {
            // This code will be executed only if all items are instances of WorkerInterface
        }
    }
}
```

::: warning
Please note that with the `ensureClass()` method, the exception will be thrown only when iterating through the generator.
:::

### CollectorHelper::orderHigherPriorityFirst()

The `orderHigherPriorityFirst()` method will ensure the object with the highest priority is placed first, and the object
with the lowest priority is placed last.

In order to define an object's priority, it must implement the `EonX\EasyUtils\Interfaces\HasPriorityInterface` provided
by this package. If an object doesn't implement this interface then its priority will default to `0` automatically.

```php
// Foo and Bar both implement EonX\EasyUtils\Interfaces\HasPriorityInterface

$foo = new Foo(); // Has a priority of 10
$bar = new Bar(); // Has a priority of 100

// $foo is added to the array first, and $bar second
$objects = [$foo, $bar];

// $bar is now first as it has a higher priority than $foo
$objects = CollectorHelper::orderHigherPriorityFirst($objects); // [$bar, $foo]
```

::: tip
The `orderHigherPriorityFirst()` method still returns an iterable (or, more precisely, a generator). If you need an `array`, you
can use the `orderHigherPriorityFirstAsArray()` method instead.
:::

### CollectorHelper::orderLowerPriorityFirst()

The `orderLowerPriorityFirst()` method is the opposite of `orderHigherPriorityFirst()`. It will ensure the object with
the lowest priority is placed first, and the object with the highest priority is placed last.

In order to define an object's priority, it must implement the `EonX\EasyUtils\Interfaces\HasPriorityInterface` provided
by this package. If an object doesn't implement this interface then its priority will default to `0` automatically.

```php
// Foo and Bar both implement EonX\EasyUtils\Interfaces\HasPriorityInterface

$foo = new Foo(); // Has a priority of 10
$bar = new Bar(); // Has a priority of 100

// $foo is added to the array first, and $bar second
$objects = [$foo, $bar];

// $foo is still first as it has a lower priority than $bar
$objects = CollectorHelper::orderLowerPriorityFirst($objects); // [$foo, $bar]
```

::: tip
The `orderLowerPriorityFirst()` method still returns an iterable (or, more precisely, a generator). If you need an `array`, you
can use the `orderLowerPriorityFirstAsArray()` method instead.
:::

## Math

The Math helper provides the following methods:

- `abs:` returns the absolute value for the given number
- `add:` adds two numbers and returns the result
- `comp:` compares two numbers
- `divide:` divides one number by the other and returns the result
- `multiply:` multiplies one number by the other and returns the result
- `round:` rounds the given number and returns the result
- `sub:` subs tow numbers and returns the result

## SensitiveDataSanitizer

There are two types of object transformers:

- `EonX\EasyUtils\SensitiveData\ObjectTransformers\DefaultObjectTransformer`: this is the default object transformer
  that simply transforms the given object to an array using json_encode/json_decode hack. It will not transform private
  properties of given object
- `EonX\EasyUtils\Bridge\Symfony\SensitiveData\ObjectTransformers\NormalizerObjectTransformer`: this object transformer uses Symfony's
  Serializer component to transform given object to array. It will transform private properties of given object

By default, the `DefaultObjectTransformer` is used, and it has priority 10000
If you want to use `NormalizerObjectTransformer` or change the priority of object transformers, you should set it in your DI configuration.
Transformer with the lowest priority will be used first
For example:

```php
$services
    ->set(NormalizerObjectTransformer::class)
    ->arg('$priority', 100);
```

To set priority of object transformers, you can use `EonX\EasyUtils\SensitiveData\ObjectTransformerPriorities` class.

[1]: https://getcomposer.org/

[2]: https://tomasvotruba.com/blog/2018/06/14/collector-pattern-for-dummies/

[3]: https://book.cakephp.org/4.next/en/development/dependency-injection.html#tagging-services

[4]: https://symfony.com/doc/current/service_container/tags.html

[5]: https://laravel.com/docs/8.x/container#tagging

[6]: https://symfony.com/blog/new-in-symfony-3-3-simpler-service-configuration#interface-based-service-configuration
