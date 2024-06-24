---eonx_docs---
title: Introduction
weight: 0
---eonx_docs---

The purpose of this package isn't to be used within a project by the application as there is no point in creating
another level of abstraction in that case BUT only to allow eonx-com packages to dispatch events without
having to think about the event dispatcher used by each of our projects.

### Require package (Composer)

The recommended way to install this package is to use [Composer][1]:

```bash
$ composer require eonx-com/easy-event-dispatcher
```

<br>

### Usage

This package will register the `EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface` within the DI container,
allowing you to use dependency injection to dispatch your events.

```php
// src/MyService.php

namespace App;

use App\MyEvent;use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;

final class MyService
{
    /**
     * @var \EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function myMethod(string $myParam): void
    {
        $this->eventDispatcher->dispatch(new MyEvent($myParam));
    }
}
```

[1]: https://getcomposer.org/
