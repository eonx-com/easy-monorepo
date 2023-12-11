---eonx_docs---
title: EntityManager Decorator
weight: 3001
is_section: true
---eonx_docs---

## EntityManagerDecorator

Two main features of EntityManagerDecorator are Transactional and Deferred entity event managing (dispatching or clearing).

### Transactional

`$entityManager->transactional(callable $callback)` provide:

- flush and commit or rollback if something goes wrong
- close EntityManager if `Doctrine\ORM\ORMException` or `Doctrine\DBAL\Exception` is thrown

#### Configuration

Register the decorator

```yaml
services:
    EonX\EasyDoctrine\ORM\Decorators\EntityManagerDecorator:
        arguments:
            $decorated: '@.inner'
        decorates: doctrine.orm.default_entity_manager
```

### Deferred entity event dispatcher

This feature allows you to defer dispatching of Doctrine insertions and updates events.

Events **dispatched** when making `commit` of the lowest transaction level.

Events **cleared** when making `rollback` of the lowest transaction level.

#### Configuration

```yaml
easy_doctrine:
    entities:
        - 'App\Entity\SomeEntity'
        - 'App\Entity\AnotherEntity'
```

```yaml
services:
    EonX\EasyDoctrine\Listeners\EntityOnFlushEventListener:
        arguments:
            $subscribedEntities: '%easy_doctrine.entities%'
```

#### Events

`DeferredEntityEventDispatcher` dispatch `EntityCreatedEvent` and `EntityUpdatedEvent` events.

Register a listener:

```yaml
services:
    App\Listener\SomeEntityCreatedListener:
        tags:
            -   name: kernel.event_listener
                event: EonX\EasyDoctrine\Events\EntityCreatedEvent

    App\Listener\SomeEntityUpdatedListener:
        tags:
            -   name: kernel.event_listener
                event: EonX\EasyDoctrine\Events\EntityUpdatedEvent
```

Listener example:

```php
<?php
declare(strict_types=1);

namespace App\Listener;

use App\Entity\SomeEntity;

final class SomeEntityCreatedListener
{
    public function __invoke(EntityCreatedEvent $event): void
    {
        $entity = $event->getEntity();

        if (\is_a($entity, SomeEntity::class)) {
            // do something
        }
    }
}

```

#### Temporarily disable deferred dispatching.

```php
final class SomeService
{
    private DeferredEntityEventDispatcherInterface $deferredEntityEventDispatcher;

    public function __construct(
        DeferredEntityEventDispatcherInterface $deferredEntityEventDispatcher
    ) {
        $this->deferredEntityEventDispatcher = $deferredEntityEventDispatcher;
    }

    public function someMethod(): void
    {
        $this->deferredEntityEventDispatcher->disable();

        try {

            //execute code here
            //insert and updates executed immediately

        } finally {
            $this->deferredEntityEventDispatcher->enable();
        }
    }
```
