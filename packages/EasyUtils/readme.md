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

### CollectorHelper::orderHigherPriorityFirst()

Most popular PHP frameworks provide features to tag services, and then define all services for a specific tag as
dependencies to other services. For examples, you can have a look at the following resources:

- [Tagging Services in CakePHP][3]
- [How to Work with Service Tags in Symfony][4]
- [Service Tagging in Laravel][5]

Those features are great to start implementing the [Collector DesignPattern][2] in your project as it allows you to
easily inject a collection of services sharing the same tag into other services.

However, there are things you need to consider:

- You have no guarantee all given services are instances of a specific class/interface
- You have no control on the order the services are organised within the given collection

Let's elaborate on the above points.

#### No guarantee on the content of tagged services

The service tagging features provided discussed above are great, but they do not allow you to ensure all services sharing
the same tagged meet common criteria. Symfony has a feature to [automatically tag services based on their class][6],
however nothing stop you from manually tag a service with the same tag or even one of your dependencies.

This is why we strongly recommend to always filter the given `iterable` of services by a given class/interface of your
choice using the `filterByClass()` or `filterByClassAsArray()` methods.

#### No control on the order the services are organised

If you have used those service tagging features before, you would have probably realised that you can control the order
the services are organised by simply change the order you define the services themselves. However, same issue as above,
there is nothing stopping you or one of your dependencies to tag a service with the same tag and therefore you cannot
guarantee the order as you cannot modify the dependencies service definitions.

Don't panic, the `CollectorHelper` is here for us!


In some cases the order of the given services does not matter, so no need to do anything. If your logic requires
the services to be used in a specific order, then the `orderHigherPriorityFirst()` and `orderLowerPriorityFirst()`
are for you!

The above methods will sort the objects within the given `iterable` based on their priority. In order to define its
priority, an object must implement the `EonX\EasyUtils\Interfaces\HasPriorityInterface` provided by this package. If
an object doesn't implement this interface then its priority will default to `0` automatically.

The `orderHigherPriorityFirst()` method will make sure the object with the highest priority is placed first, and the
object with the lowest priority is placed last.

```php
// Foo and Bar both implement EonX\EasyUtils\Interfaces\HasPriorityInterface

$foo = new Foo(); // Has a priority of 10
$bar = new Bar(); // Has a priority of 100

// $foo is added to the array first, and $bar second
$objects = [$foo, $bar];

// $bar is now first as it has a higher priority than $foo
$objects = CollectorHelper::orderHigherPriorityFirst($objects); // [$bar, $foo]
```

### CollectorHelper::orderLowerPriorityFirst()

The `orderLowerPriorityFirst()` is the opposite of `orderHigherPriorityFirst()`, it will make sure the object will the
lowest priority is place first, and the object with the highest priority is placed last.

Let's have a look at the preview example but using `orderLowerPriorityFirst()` this time.

```php
// Foo and Bar both implement EonX\EasyUtils\Interfaces\HasPriorityInterface

$foo = new Foo(); // Has a priority of 10
$bar = new Bar(); // Has a priority of 100

// $foo is added to the array first, and $bar second
$objects = [$foo, $bar];

// $foo is still first as it has a lower priority than $bar
$objects = CollectorHelper::orderLowerPriorityFirst($objects); // [$foo, $bar]
```

[1]: https://getcomposer.org/
[2]: https://tomasvotruba.com/blog/2018/06/14/collector-pattern-for-dummies/
[3]: https://book.cakephp.org/4.next/en/development/dependency-injection.html#tagging-services
[4]: https://symfony.com/doc/current/service_container/tags.html
[5]: https://laravel.com/docs/8.x/container#tagging
[6]: https://symfony.com/blog/new-in-symfony-3-3-simpler-service-configuration#interface-based-service-configuration
