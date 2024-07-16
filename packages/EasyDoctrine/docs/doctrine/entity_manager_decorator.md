---eonx_docs---
title: EntityManager Decorator
weight: 3001
is_section: true
---eonx_docs---

## EntityManagerDecorator

Two main features of EntityManagerDecorator are Transactional and Deferred entity event managing (dispatching or clearing).

### Transactional

`$entityManager->wrapInTransaction(callable $callback)` provide:

- flush and commit or rollback if something goes wrong
- close EntityManager if `Doctrine\ORM\ORMException` or `Doctrine\DBAL\Exception` is thrown

#### Configuration

Register the decorator

```php
<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\EntityEvent\EntityManager\WithEventsEntityManager;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(WithEventsEntityManager::class)
        ->arg('$decorated', service('.inner'))
        ->decorate('doctrine.orm.default_entity_manager');
};

```

### Deferred entity event dispatcher

This feature allows you to defer dispatching of Doctrine insertions and updates events.

Events **dispatched** when making `commit` of the lowest transaction level.

Events **cleared** when making `rollback` of the lowest transaction level.

#### Configuration

```php
<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Entity\SomeEntity;
use App\Entity\AnotherEntity;
use Symfony\Config\EasyDoctrineConfig;

return static function (EasyDoctrineConfig $easyDoctrineConfig): void {
    $easyDoctrineConfig
        ->deferredDispatcherEntities([
            SomeEntity::class,
            AnotherEntity::class,
        ]);
};

```

```php
<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\EntityEvent\Listener\EntityEventListener;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(EntityEventListener::class)
        ->arg('$entities', param('easy_doctrine.deferred_dispatcher_entities'));
};

```

#### Events

`DeferredEntityEventDispatcher` dispatch the following events:

- `EntityCreatedEvent`
- `EntityDeletedEvent`
- `EntityUpdatedEvent`

```php
<?php
declare(strict_types=1);

namespace App\Listener;

use App\Entity\SomeEntity;
use EonX\EasyDoctrine\EntityEvent\Event\EntityCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(EntityCreatedEvent::class)]
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
