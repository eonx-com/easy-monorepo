<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Subscribers;

use Doctrine\Common\EventManager;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcher;
use EonX\EasyDoctrine\Events\EntityCreatedEvent;
use EonX\EasyDoctrine\Events\EntityUpdatedEvent;
use EonX\EasyDoctrine\Subscribers\EntityEventSubscriber;
use EonX\EasyDoctrine\Tests\AbstractTestCase;
use EonX\EasyDoctrine\Tests\Fixtures\Category;
use EonX\EasyDoctrine\Tests\Fixtures\Product;
use EonX\EasyDoctrine\Tests\Stubs\EntityManagerStub;
use EonX\EasyDoctrine\Tests\Stubs\EventDispatcherStub;

/**
 * @covers \EonX\EasyDoctrine\Subscribers\EntityEventSubscriber
 * @covers \EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcher
 */
final class EntityEventSubscriberTest extends AbstractTestCase
{
    public function testEventsAreDispatchedAfterEnablingDispatcher(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $dispatcher = new DeferredEntityEventDispatcher($eventDispatcher);
        $entityManager = EntityManagerStub::createFromDeferredEntityEventDispatcher(
            $dispatcher,
            [Product::class],
            [Product::class]
        );

        $dispatcher->disable();
        $dispatcher->enable();
        $product = new Product();
        $product->setName('Description 1');
        $product->setPrice('1000');
        $entityManager->persist($product);

        $entityManager->flush();

        $events = $eventDispatcher->getDispatchedEvents();
        self::assertCount(1, $events);
    }

    public function testEventsAreDispatchedForEntitiesAddedLater(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $dispatcher = new DeferredEntityEventDispatcher($eventDispatcher);
        $eventSubscriber = new EntityEventSubscriber($dispatcher, []);
        $eventManager = new EventManager();
        $eventManager->addEventSubscriber($eventSubscriber);
        $entityManager = EntityManagerStub::createFromEventManager($eventManager, [Product::class]);

        $eventSubscriber->addAcceptableEntity(Product::class);
        $eventSubscriber->addAcceptableEntity(Product::class);

        $product = new Product();
        $product->setName('Product 1');
        $product->setPrice('1000');
        $entityManager->persist($product);
        $entityManager->flush();
        $events = $eventDispatcher->getDispatchedEvents();
        self::assertCount(1, $events);
    }

    public function testEventsAreDispatchedWhenExceptionIsThrownAndCatched(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $entityManager = EntityManagerStub::createFromSymfonyEventDispatcher(
            $eventDispatcher,
            [Product::class],
            [Product::class]
        );

        $product = new Product();

        $entityManager->transactional(function () use ($entityManager, $product) {
            $product->setName('Description 1');
            $product->setPrice('1000');
            $entityManager->persist($product);
            $entityManager->flush();
            try {
                $entityManager->transactional(function () use ($entityManager, $product) {
                    $product->setPrice('2000');
                    $entityManager->persist($product);
                    $entityManager->flush();
                    throw new \RuntimeException('Test', 1);
                });
            } catch (\RuntimeException $exception) {
            }
        });

        $events = $eventDispatcher->getDispatchedEvents();
        self::assertCount(1, $events);
        self::assertEqualsCanonicalizing(
            new EntityCreatedEvent(
                $product,
                [
                    'category' => [null, null],
                    'name' => [null, 'Description 1'],
                    'price' => [null, '1000'],
                ]
            ),
            $events[0]
        );
    }

    public function testEventsAreDispatchedWhenMultipleEntitiesAreChanged(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $entityManager = EntityManagerStub::createFromSymfonyEventDispatcher(
            $eventDispatcher,
            [Category::class, Product::class],
            [Category::class, Product::class]
        );
        $category = new Category();
        $category->setName('Computer');
        $entityManager->persist($category);
        $product = new Product();
        $product->setName('Keyboard');
        $product->setPrice('1000');
        $entityManager->persist($product);

        $entityManager->flush();

        $events = $eventDispatcher->getDispatchedEvents();
        self::assertCount(2, $events);
        self::assertEqualsCanonicalizing(
            new EntityCreatedEvent(
                $category,
                [
                    'name' => [null, 'Computer'],
                ]
            ),
            $events[0]
        );
        self::assertEqualsCanonicalizing(
            new EntityCreatedEvent(
                $product,
                [
                    'name' => [null, 'Keyboard'],
                    'price' => [null, '1000'],
                    'category' => [null, null],
                ]
            ),
            $events[1]
        );
    }

    public function testEventsAreDispatchedWhenMultipleEntitiesAreUpdated(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $entityManager = EntityManagerStub::createFromSymfonyEventDispatcher(
            $eventDispatcher,
            [Category::class, Product::class],
            [Category::class, Product::class]
        );
        $entityManager->getConnection()
            ->insert(
                'category',
                [
                    'id' => 1,
                    'name' => 'Computer',
                ]
            );
        $entityManager->getConnection()
            ->insert(
                'product',
                [
                    'id' => 1,
                    'name' => 'Keyboard',
                    'price' => '1000',
                ]
            );
        /** @var Category $category */
        $category = $entityManager->getRepository(Category::class)->find(1);
        /** @var Product $product */
        $product = $entityManager->getRepository(Product::class)->find(1);
        $category->setName('Computer Peripherals');
        $product->setPrice('2000');

        $entityManager->persist($category);
        $entityManager->persist($product);
        $entityManager->flush();

        $events = $eventDispatcher->getDispatchedEvents();
        self::assertCount(2, $events);
        self::assertEqualsCanonicalizing(
            new EntityUpdatedEvent(
                $category,
                [
                    'name' => ['Computer', 'Computer Peripherals'],
                ]
            ),
            $events[0]
        );
        self::assertEqualsCanonicalizing(
            new EntityUpdatedEvent(
                $product,
                [
                    'price' => ['1000', '2000'],
                ]
            ),
            $events[1]
        );
    }

    public function testEventsAreDispatchedWithRelatedEntityInChangeSet(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $entityManager = EntityManagerStub::createFromSymfonyEventDispatcher(
            $eventDispatcher,
            [Product::class],
            [Product::class, Category::class]
        );

        $category = new Category();
        $category->setName('Computer');
        $entityManager->persist($category);
        $product = new Product();
        $product->setName('Keyboard');
        $product->setPrice('1000');
        $product->setCategory($category);
        $entityManager->persist($product);

        $entityManager->flush();

        $events = $eventDispatcher->getDispatchedEvents();
        self::assertCount(1, $events);
        self::assertEqualsCanonicalizing(
            new EntityCreatedEvent(
                $product,
                [
                    'name' => [null, 'Keyboard'],
                    'price' => [null, '1000'],
                    'category' => [null, $category],
                ]
            ),
            $events[0]
        );
    }

    public function testEventsAreNotDispatchedWhenDispatcherIsDisabled(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $dispatcher = new DeferredEntityEventDispatcher($eventDispatcher);
        $entityManager = EntityManagerStub::createFromDeferredEntityEventDispatcher(
            $dispatcher,
            [Product::class],
            [Product::class]
        );

        $dispatcher->disable();
        $product = new Product();
        $product->setName('Description 1');
        $product->setPrice('1000');
        $entityManager->persist($product);
        $entityManager->flush();
        $product->setPrice('2000');
        $entityManager->flush();

        $events = $eventDispatcher->getDispatchedEvents();
        self::assertCount(0, $events);
    }

    public function testEventsAreNotDispatchedWhenExceptionIsThrown(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $entityManager = EntityManagerStub::createFromSymfonyEventDispatcher(
            $eventDispatcher,
            [Product::class],
            [Product::class]
        );

        $this->safeCall(function () use ($entityManager) {
            $product = new Product();

            $entityManager->transactional(function () use ($entityManager, $product) {
                $product->setName('Description 1');
                $product->setPrice('1000');
                $entityManager->persist($product);
                $entityManager->flush();
                $entityManager->transactional(function () use ($entityManager, $product) {
                    $product->setPrice('2000');
                    $entityManager->persist($product);
                    $entityManager->flush();
                    throw new \RuntimeException('Test', 1);
                });
            });

            $entityManager->flush();
        });

        $this->assertThrownException(\RuntimeException::class, 1);
        $events = $eventDispatcher->getDispatchedEvents();
        self::assertCount(0, $events);
    }

    public function testEventsAreNotDispatchedWhenNoChangesMade(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $entityManager = EntityManagerStub::createFromSymfonyEventDispatcher(
            $eventDispatcher,
            [Product::class]
        );

        $entityManager->flush();

        $events = $eventDispatcher->getDispatchedEvents();
        self::assertCount(0, $events);
    }

    public function testEventsAreNotDispatchedWhenRelatedEntitiesAreChanged(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $entityManager = EntityManagerStub::createFromSymfonyEventDispatcher(
            $eventDispatcher,
            [Product::class],
            [Product::class, Category::class]
        );
        $entityManager->getConnection()
            ->insert(
                'category',
                [
                    'id' => 1,
                    'name' => 'Computer',
                ]
            );
        $entityManager->getConnection()
            ->insert(
                'product',
                [
                    'id' => 1,
                    'name' => 'Keyboard',
                    'price' => '1000',
                    'category_id' => 1,
                ]
            );

        /** @var Product $product */
        $product = $entityManager->getRepository(Product::class)->find(1);
        /** @var Category $category */
        $category = $product->getCategory();
        $category->setName('Computer Peripherals');

        $entityManager->flush();

        $events = $eventDispatcher->getDispatchedEvents();
        self::assertCount(0, $events);
    }

    public function testEventsAreNotDispatchedWhenSubscriptionDoesNotExist(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $entityManager = EntityManagerStub::createFromSymfonyEventDispatcher(
            $eventDispatcher,
            [],
            [Product::class]
        );
        $product = new Product();
        $product->setName('Product 1');
        $product->setPrice('1000');
        $entityManager->persist($product);

        $entityManager->flush();

        self::assertCount(0, $eventDispatcher->getDispatchedEvents());
    }

    public function testOneEventIsDispatchedForMultipleUpdatedEntity(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $entityManager = EntityManagerStub::createFromSymfonyEventDispatcher(
            $eventDispatcher,
            [Product::class],
            [Product::class]
        );
        $entityManager->getConnection()
            ->insert(
                'product',
                [
                    'id' => 1,
                    'name' => 'Keyboard',
                    'price' => '1000',
                ]
            );
        /** @var Product $product */
        $product = $entityManager->getRepository(Product::class)->find(1);

        $entityManager->transactional(function () use ($entityManager, $product) {
            $product->setPrice('2000');
            $entityManager->persist($product);
            $entityManager->flush();
            $entityManager->transactional(function () use ($entityManager, $product) {
                $product->setPrice('3000');
                $product->setName('Keyboard 2');
                $entityManager->flush();
            });
        });
        $entityManager->flush();

        $events = $eventDispatcher->getDispatchedEvents();
        self::assertCount(1, $events);
        self::assertEqualsCanonicalizing(
            new EntityUpdatedEvent(
                $product,
                [
                    'price' => ['1000', '3000'],
                    'name' => ['Keyboard', 'Keyboard 2'],
                ]
            ),
            $events[0]
        );
    }

    public function testOneEventIsDispatchedForNewEntity(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $entityManager = EntityManagerStub::createFromSymfonyEventDispatcher(
            $eventDispatcher,
            [Product::class],
            [Product::class]
        );

        $product = new Product();
        $entityManager->transactional(function () use ($entityManager, $product) {
            $product->setName('Description 1');
            $product->setPrice('1000');
            $entityManager->persist($product);
            $entityManager->flush();
            $entityManager->transactional(function () use ($entityManager, $product) {
                $product->setPrice('2000');
                $entityManager->flush();
            });
        });
        $entityManager->flush();

        $events = $eventDispatcher->getDispatchedEvents();
        self::assertCount(1, $events);
        self::assertEqualsCanonicalizing(
            new EntityCreatedEvent(
                $product,
                [
                    'description' => [null, 'Description 1'],
                    'price' => [null, '2000'],
                    'category' => [null, null],
                ]
            ),
            $events[0]
        );
    }
}
