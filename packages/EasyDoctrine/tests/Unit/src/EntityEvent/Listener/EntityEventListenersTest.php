<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Unit\EntityEvent\Listener;

use DateTimeImmutable;
use DateTimeZone;
use EonX\EasyDoctrine\Bundle\Factory\ObjectCopierFactory;
use EonX\EasyDoctrine\EntityEvent\Dispatcher\DeferredEntityEventDispatcher;
use EonX\EasyDoctrine\EntityEvent\Event\EntityCreatedEvent;
use EonX\EasyDoctrine\EntityEvent\Event\EntityDeletedEvent;
use EonX\EasyDoctrine\EntityEvent\Event\EntityUpdatedEvent;
use EonX\EasyDoctrine\EntityEvent\Listener\EntityEventListener;
use EonX\EasyDoctrine\Tests\Fixture\Entity\Category;
use EonX\EasyDoctrine\Tests\Fixture\Entity\Offer;
use EonX\EasyDoctrine\Tests\Fixture\Entity\Product;
use EonX\EasyDoctrine\Tests\Fixture\Entity\Tag;
use EonX\EasyDoctrine\Tests\Fixture\ValueObject\Price;
use EonX\EasyDoctrine\Tests\Stub\EntityManager\EntityManagerStub;
use EonX\EasyDoctrine\Tests\Stub\EventDispatcher\EventDispatcherStub;
use EonX\EasyDoctrine\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;

#[CoversClass(DeferredEntityEventDispatcher::class)]
#[CoversClass(EntityEventListener::class)]
final class EntityEventListenersTest extends AbstractUnitTestCase
{
    public function testEventIsDispatchedIfTimezoneWasChanged(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $entityManager = EntityManagerStub::createFromSymfonyEventDispatcher(
            $eventDispatcher,
            [Category::class],
            [Category::class]
        );
        $entityManager->getConnection()
            ->insert(
                'category',
                [
                    'id' => 1,
                    'name' => 'Computer',
                    'activeTill' => '2022-12-20 16:23:52',
                ]
            );
        /** @var \EonX\EasyDoctrine\Tests\Fixture\Entity\Category $category */
        $category = $entityManager->getRepository(Category::class)->find(1);
        /** @var \DateTime $activeTill */
        $activeTill = $category->getActiveTill();
        $newActiveTill = (clone $activeTill)->setTimezone(new DateTimeZone('Asia/Krasnoyarsk'));
        $category->setActiveTill($newActiveTill);

        $entityManager->persist($category);
        $entityManager->flush();

        $events = $eventDispatcher->getDispatchedEvents();
        self::assertCount(1, $events);
        self::assertEqualsCanonicalizing(
            new EntityUpdatedEvent(
                $category,
                [
                    'activeTill' => [$activeTill, $newActiveTill],
                ]
            ),
            $events[0]
        );
    }

    public function testEventIsNotDispatchedForEqualObjects(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $entityManager = EntityManagerStub::createFromSymfonyEventDispatcher(
            $eventDispatcher,
            [Category::class, Product::class],
            [Category::class, Product::class]
        );
        $activeTill = '2022-12-20 16:23:52';
        $entityManager->getConnection()
            ->insert(
                'category',
                [
                    'id' => 1,
                    'name' => 'Computer',
                    'activeTill' => $activeTill,
                ]
            );
        $entityManager->getConnection()
            ->insert(
                'product',
                [
                    'id' => 1,
                    'name' => 'Laptop',
                    'price' => '1000 USD',
                ]
            );
        /** @var \EonX\EasyDoctrine\Tests\Fixture\Entity\Category $category */
        $category = $entityManager->getRepository(Category::class)->find(1);
        $category->setActiveTill(new DateTimeImmutable($activeTill));
        /** @var \EonX\EasyDoctrine\Tests\Fixture\Entity\Product $product */
        $product = $entityManager->getRepository(Product::class)->find(1);
        $product->setPrice(new Price('1000', 'USD'));

        $entityManager->flush();

        $events = $eventDispatcher->getDispatchedEvents();
        self::assertCount(0, $events);
    }

    public function testEventsAreDispatchedAfterEnablingDispatcher(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $dispatcher = new DeferredEntityEventDispatcher($eventDispatcher, ObjectCopierFactory::create());
        $entityManager = EntityManagerStub::createFromDeferredEntityEventDispatcher(
            $dispatcher,
            [Product::class],
            [Product::class]
        );

        $dispatcher->disable();
        $dispatcher->enable();
        $product = new Product();
        $product->setName('Description 1');
        $product->setPrice(new Price('1000', 'USD'));
        $entityManager->persist($product);

        $entityManager->flush();

        $events = $eventDispatcher->getDispatchedEvents();
        self::assertCount(1, $events);
    }

    public function testEventsAreDispatchedWhenExceptionIsThrownAndCaught(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $entityManager = EntityManagerStub::createFromSymfonyEventDispatcher(
            $eventDispatcher,
            [Product::class],
            [Product::class]
        );

        $product = new Product();

        $entityManager->wrapInTransaction(function () use ($entityManager, $product): void {
            $product->setName('Description 1');
            $product->setPrice(new Price('1000', 'USD'));
            $entityManager->persist($product);
            $entityManager->flush();

            try {
                $entityManager->wrapInTransaction(function () use ($entityManager, $product): never {
                    $product->setPrice(new Price('2000', 'USD'));
                    $entityManager->persist($product);
                    $entityManager->flush();

                    throw new RuntimeException('Test', 1);
                });
            } catch (RuntimeException) {
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
                    'price' => [null, new Price('1000', 'USD')],
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
        $price = new Price('1000', 'USD');
        $product->setPrice($price);
        $entityManager->persist($product);

        $entityManager->flush();

        $events = $eventDispatcher->getDispatchedEvents();
        self::assertCount(2, $events);
        self::assertEqualsCanonicalizing(
            new EntityCreatedEvent(
                $category,
                [
                    'activeTill' => [null, null],
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
                    'price' => [null, $price],
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
                    'price' => '1000 USD',
                ]
            );
        /** @var \EonX\EasyDoctrine\Tests\Fixture\Entity\Category $category */
        $category = $entityManager->getRepository(Category::class)->find(1);
        /** @var \EonX\EasyDoctrine\Tests\Fixture\Entity\Product $product */
        $product = $entityManager->getRepository(Product::class)->find(1);
        $category->setName('Computer Peripherals');
        $product->setPrice(new Price('2000', 'USD'));

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
                    'price' => [new Price('1000', 'USD'), new Price('2000', 'USD')],
                ]
            ),
            $events[1]
        );
    }

    public function testEventsAreDispatchedWithFlushInEmbeddedEventHandler(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $entityManager = EntityManagerStub::createFromSymfonyEventDispatcher(
            $eventDispatcher,
            [Category::class, Product::class],
            [Category::class, Product::class]
        );
        $eventDispatcher->addDispatchCallback(
            class: EntityCreatedEvent::class,
            callback: static function (EntityCreatedEvent $event) use ($entityManager): void {
                if ($event->getEntity() instanceof Category === false) {
                    return;
                }
                $product = new Product();
                $product->setName('Keyboard');
                $product->setPrice(new Price('100', 'USD'));

                $entityManager->persist($product);
                $entityManager->flush();
            }
        );
        $category = new Category();
        $category->setName('Computer');
        $entityManager->persist($category);

        $entityManager->flush();

        $events = $eventDispatcher->getDispatchedEvents();
        self::assertCount(2, $events);
        self::assertEqualsCanonicalizing(
            new EntityCreatedEvent(
                $category,
                [
                    'activeTill' => [null, null],
                    'name' => [null, 'Computer'],
                ]
            ),
            $events[0]
        );
        /** @var \EonX\EasyDoctrine\Tests\Fixture\Entity\Product $product */
        $product = $entityManager->getRepository(Product::class)->findOneBy(['name' => 'Keyboard']);
        self::assertEqualsCanonicalizing(
            new EntityCreatedEvent(
                $product,
                [
                    'name' => [null, 'Keyboard'],
                    'price' => [null, new Price('100', 'USD')],
                    'category' => [null, null],
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
        $product->setPrice(new Price('1000', 'USD'));
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
                    'price' => [null, new Price('1000', 'USD')],
                    'category' => [null, $category],
                ]
            ),
            $events[0]
        );
    }

    public function testEventsAreNotDispatchedForCollectionWhenItemUpdated(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $entityManager = EntityManagerStub::createFromSymfonyEventDispatcher(
            $eventDispatcher,
            [Product::class, Tag::class],
            [Product::class, Tag::class]
        );
        $entityManager->getConnection()
            ->insert(
                'product',
                [
                    'id' => 1,
                    'name' => 'Keyboard',
                    'price' => '1000 USD',
                ]
            );
        $entityManager->getConnection()
            ->insert(
                'tag',
                [
                    'id' => 1,
                    'name' => 'Tag 1',
                    'product_id' => 1,
                ]
            );
        $entityManager->getConnection()
            ->insert(
                'tag',
                [
                    'id' => 2,
                    'name' => 'Tag 2',
                    'product_id' => 1,
                ]
            );

        /** @var \EonX\EasyDoctrine\Tests\Fixture\Entity\Product $product */
        $product = $entityManager->getRepository(Product::class)->find(1);
        /** @var \EonX\EasyDoctrine\Tests\Fixture\Entity\Tag $tag2 */
        $tag2 = $entityManager->getRepository(Tag::class)->find(2);
        $product->getTags()
            ->toArray();
        $tag2->setName('New Tag 2 Name');
        $entityManager->flush();

        $events = $eventDispatcher->getDispatchedEvents();
        self::assertCount(1, $events);
        self::assertEqualsCanonicalizing(
            new EntityUpdatedEvent(
                $tag2,
                [
                    'name' => ['Tag 2', 'New Tag 2 Name'],
                ]
            ),
            $events[0]
        );
    }

    public function testEventsAreNotDispatchedWhenDispatcherIsDisabled(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $dispatcher = new DeferredEntityEventDispatcher($eventDispatcher, ObjectCopierFactory::create());
        $entityManager = EntityManagerStub::createFromDeferredEntityEventDispatcher(
            $dispatcher,
            [Product::class],
            [Product::class]
        );

        $dispatcher->disable();
        $product = new Product();
        $product->setName('Description 1');
        $product->setPrice(new Price('1000', 'USD'));
        $entityManager->persist($product);
        $entityManager->flush();
        $product->setPrice(new Price('2000', 'USD'));
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

        $this->safeCall(function () use ($entityManager): void {
            $product = new Product();

            $entityManager->wrapInTransaction(function () use ($entityManager, $product): void {
                $product->setName('Description 1');
                $product->setPrice(new Price('1000', 'USD'));
                $entityManager->persist($product);
                $entityManager->flush();
                $entityManager->wrapInTransaction(function () use ($entityManager, $product): never {
                    $product->setPrice(new Price('2000', 'USD'));
                    $entityManager->persist($product);
                    $entityManager->flush();

                    throw new RuntimeException('Test', 1);
                });
            });

            $entityManager->flush();
        });

        $this->assertThrownException(RuntimeException::class, 1);
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
                    'price' => '1000 USD',
                    'category_id' => 1,
                ]
            );

        /** @var \EonX\EasyDoctrine\Tests\Fixture\Entity\Product $product */
        $product = $entityManager->getRepository(Product::class)->find(1);
        /** @var \EonX\EasyDoctrine\Tests\Fixture\Entity\Category $category */
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
        $product->setPrice(new Price('1000', 'USD'));
        $entityManager->persist($product);

        $entityManager->flush();

        self::assertCount(0, $eventDispatcher->getDispatchedEvents());
    }

    public function testOneEventIsDispatchedForDeletedEntity(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $entityManager = EntityManagerStub::createFromSymfonyEventDispatcher(
            $eventDispatcher,
            [Product::class],
            [Product::class, Category::class, Tag::class, Offer::class]
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
                    'price' => '1000 USD',
                    'category_id' => 1,
                ]
            );
        $entityManager->getConnection()
            ->insert(
                'tag',
                [
                    'id' => 1,
                    'name' => 'Tag 1',
                    'product_id' => 1,
                ]
            );
        $entityManager->getConnection()
            ->insert(
                'tag',
                [
                    'id' => 2,
                    'name' => 'Tag 2',
                    'product_id' => 1,
                ]
            );

        /** @var \EonX\EasyDoctrine\Tests\Fixture\Entity\Product $product */
        $product = $entityManager->getRepository(Product::class)->find(1);

        $entityManager->remove($product);
        $entityManager->flush();

        $events = $eventDispatcher->getDispatchedEvents();
        self::assertCount(1, $events);
        /** @var \EonX\EasyDoctrine\EntityEvent\Event\EntityDeletedEvent $actualEvent */
        $actualEvent = $events[0];
        self::assertInstanceOf(EntityDeletedEvent::class, $actualEvent);
        self::assertEquals($actualEvent->getChangeSet(), [
            'id' => [1, null],
            'name' => ['Keyboard', null],
            'price' => [new Price('1000', 'USD'), null],
            'category_id' => [1, null],
            'category' => [$product->getCategory(), null],
            'tags' => [$product->getTags(), null],
            'offers' => [$product->getOffers(), null],
        ]);
        /** @var \EonX\EasyDoctrine\Tests\Fixture\Entity\Product $product */
        $product = $actualEvent->getEntity();
        self::assertInstanceOf(Product::class, $product);
        self::assertSame(1, $product->getId());
        self::assertSame('Keyboard', $product->getName());
        self::assertEquals(new Price('1000', 'USD'), $product->getPrice());
        self::assertNotNull($product->getCategory());
        /** @var \EonX\EasyDoctrine\Tests\Fixture\Entity\Category $category */
        $category = $product->getCategory();
        self::assertSame(1, $category->getId());
        self::assertSame('Computer', $category->getName());
        /** @var \EonX\EasyDoctrine\Tests\Fixture\Entity\Tag $tag1 */
        $tag1 = $product->getTags()[0];
        self::assertSame(1, $tag1->getId());
        self::assertSame('Tag 1', $tag1->getName());
        /** @var \EonX\EasyDoctrine\Tests\Fixture\Entity\Tag $tag2 */
        $tag2 = $product->getTags()[1];
        self::assertSame(2, $tag2->getId());
        self::assertSame('Tag 2', $tag2->getName());
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
                    'price' => '1000 USD',
                ]
            );
        /** @var \EonX\EasyDoctrine\Tests\Fixture\Entity\Product $product */
        $product = $entityManager->getRepository(Product::class)->find(1);

        $entityManager->wrapInTransaction(function () use ($entityManager, $product): void {
            $product->setPrice(new Price('2000', 'USD'));
            $entityManager->persist($product);
            $entityManager->flush();
            $entityManager->wrapInTransaction(function () use ($entityManager, $product): void {
                $product->setPrice(new Price('3000', 'USD'));
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
                    'price' => [new Price('1000', 'USD'), new Price('3000', 'USD')],
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
        $entityManager->wrapInTransaction(function () use ($entityManager, $product): void {
            $product->setName('Description 1');
            $product->setPrice(new Price('1000', 'USD'));
            $entityManager->persist($product);
            $entityManager->flush();
            $entityManager->wrapInTransaction(function () use ($entityManager, $product): void {
                $product->setPrice(new Price('2000', 'USD'));
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
                    'price' => [null, new Price('2000', 'USD')],
                    'category' => [null, null],
                ]
            ),
            $events[0]
        );
    }

    public function testOneEventIsDispatchedForNewEntityCreatedAndUpdatedInTransaction(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $entityManager = EntityManagerStub::createFromSymfonyEventDispatcher(
            $eventDispatcher,
            [Product::class],
            [Product::class]
        );
        $product = new Product();
        $product->setName('Description 1');
        $product->setPrice(new Price('1000', 'USD'));

        $entityManager->persist($product);
        $entityManager->wrapInTransaction(function () use ($entityManager, $product): void {
            $product->setName('Description 2');

            $entityManager->flush();

            $product->setName('Description 3');

            $entityManager->flush();

            $product->setPrice(new Price('2000', 'AUD'));
        });

        $events = $eventDispatcher->getDispatchedEvents();
        self::assertCount(1, $events);
        self::assertEqualsCanonicalizing(
            new EntityCreatedEvent(
                $product,
                [
                    'description' => [null, 'Description 3'],
                    'price' => [null, new Price('2000', 'AUD')],
                    'category' => [null, null],
                ]
            ),
            $events[0]
        );
    }

    public function testOneEventIsDispatchedForUpdatedCollection(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $entityManager = EntityManagerStub::createFromSymfonyEventDispatcher(
            $eventDispatcher,
            [Product::class],
            [Product::class, Tag::class]
        );
        $entityManager->getConnection()
            ->insert(
                'product',
                [
                    'id' => 1,
                    'name' => 'Keyboard',
                    'price' => '1000 USD',
                ]
            );
        $entityManager->getConnection()
            ->insert(
                'tag',
                [
                    'id' => 1,
                    'name' => 'Tag 1',
                    'product_id' => 1,
                ]
            );
        $entityManager->getConnection()
            ->insert(
                'tag',
                [
                    'id' => 2,
                    'name' => 'Tag 2',
                    'product_id' => 1,
                ]
            );

        /** @var \EonX\EasyDoctrine\Tests\Fixture\Entity\Product $product */
        $product = $entityManager->getRepository(Product::class)->find(1);
        /** @var \EonX\EasyDoctrine\Tests\Fixture\Entity\Tag $tag2 */
        $tag2 = $entityManager->getRepository(Tag::class)->find(2);
        $product->getTags()
            ->toArray();
        $entityManager->remove($tag2);
        $tag3 = (new Tag())
            ->setName('Tag 3');
        $product->addTag($tag3);
        $entityManager->flush();

        $events = $eventDispatcher->getDispatchedEvents();
        self::assertCount(1, $events);
        self::assertEqualsCanonicalizing(
            new EntityUpdatedEvent(
                $product,
                [
                    'tags' => [
                        [1, 2],
                        [1, 3],
                    ],
                ]
            ),
            $events[0]
        );
    }

    public function testOneEventIsDispatchedForUpdatedCollectionWhenManyToMany(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $entityManager = EntityManagerStub::createFromSymfonyEventDispatcher(
            $eventDispatcher,
            [Product::class],
            [Product::class, Offer::class, Tag::class]
        );
        $entityManager->getConnection()
            ->insert(
                'product',
                [
                    'id' => 1,
                    'name' => 'Keyboard',
                    'price' => '1000 USD',
                ]
            );
        $entityManager->getConnection()
            ->insert(
                'offer',
                [
                    'id' => 1,
                    'name' => 'Offer 1',
                ]
            );
        $entityManager->getConnection()
            ->insert(
                'offer',
                [
                    'id' => 2,
                    'name' => 'Offer 2',
                ]
            );
        $entityManager->getConnection()
            ->insert(
                'product_offer',
                [
                    'product_id' => 1,
                    'offer_id' => 1,
                ],
            );
        $entityManager->getConnection()
            ->insert(
                'product_offer',
                [
                    'product_id' => 1,
                    'offer_id' => 2,
                ],
            );

        /** @var \EonX\EasyDoctrine\Tests\Fixture\Entity\Product $product */
        $product = $entityManager->getRepository(Product::class)->find(1);
        $product->getOffers()
            ->clear();
        $entityManager->flush();

        $events = $eventDispatcher->getDispatchedEvents();
        self::assertCount(1, $events);
        self::assertEqualsCanonicalizing(
            new EntityUpdatedEvent(
                $product,
                [
                    'offers' => [
                        ['Not available'],
                        ['Collection was cleared'],
                    ],
                ]
            ),
            $events[0]
        );
    }

    public function testOneEventIsDispatchedForUpdatedCollectionWhenTransactional(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $entityManager = EntityManagerStub::createFromSymfonyEventDispatcher(
            $eventDispatcher,
            [Product::class],
            [Product::class, Tag::class]
        );
        $entityManager->getConnection()
            ->insert(
                'product',
                [
                    'id' => 1,
                    'name' => 'Keyboard',
                    'price' => '1000 USD',
                ]
            );
        $entityManager->getConnection()
            ->insert(
                'tag',
                [
                    'id' => 1,
                    'name' => 'Tag 1',
                    'product_id' => 1,
                ]
            );
        $entityManager->getConnection()
            ->insert(
                'tag',
                [
                    'id' => 2,
                    'name' => 'Tag 2',
                    'product_id' => 1,
                ]
            );

        /** @var \EonX\EasyDoctrine\Tests\Fixture\Entity\Product $product */
        $product = $entityManager->getRepository(Product::class)->find(1);
        /** @var \EonX\EasyDoctrine\Tests\Fixture\Entity\Tag $tag2 */
        $tag2 = $entityManager->getRepository(Tag::class)->find(2);
        $product->getTags()
            ->toArray();
        $entityManager->wrapInTransaction(function () use ($entityManager, $product, $tag2): void {
            $entityManager->remove($tag2);
            $entityManager->flush();

            $entityManager->wrapInTransaction(function () use ($entityManager, $product): void {
                $tag3 = (new Tag())
                    ->setName('Tag 3');
                $product->addTag($tag3);
                $entityManager->flush();
            });
        });

        $events = $eventDispatcher->getDispatchedEvents();
        self::assertCount(1, $events);
        self::assertEqualsCanonicalizing(
            new EntityUpdatedEvent(
                $product,
                [
                    'tags' => [
                        [1, 2],
                        [1, 3],
                    ],
                ]
            ),
            $events[0]
        );
    }
}
