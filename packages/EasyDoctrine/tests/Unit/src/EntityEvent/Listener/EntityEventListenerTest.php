<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Unit\EntityEvent\Listener;

use Carbon\CarbonImmutable;
use DateTime;
use DateTimeZone;
use EonX\EasyDoctrine\EntityEvent\Dispatcher\DeferredEntityEventDispatcher;
use EonX\EasyDoctrine\EntityEvent\Dispatcher\DeferredEntityEventDispatcherInterface;
use EonX\EasyDoctrine\EntityEvent\Event\EntityCreatedEvent;
use EonX\EasyDoctrine\EntityEvent\Event\EntityDeletedEvent;
use EonX\EasyDoctrine\EntityEvent\Event\EntityUpdatedEvent;
use EonX\EasyDoctrine\EntityEvent\Listener\EntityEventListener;
use EonX\EasyDoctrine\Tests\Fixture\App\Dispatcher\EventDispatcher;
use EonX\EasyDoctrine\Tests\Fixture\App\Entity\Category;
use EonX\EasyDoctrine\Tests\Fixture\App\Entity\Product;
use EonX\EasyDoctrine\Tests\Fixture\App\Entity\Tag;
use EonX\EasyDoctrine\Tests\Fixture\App\ValueObject\Price;
use EonX\EasyDoctrine\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;

#[CoversClass(DeferredEntityEventDispatcher::class)]
#[CoversClass(EntityEventListener::class)]
final class EntityEventListenerTest extends AbstractUnitTestCase
{
    public function testEntityEventsAreNotDispatchedWhenExceptionIsThrown(): void
    {
        self::bootKernel(['environment' => 'product']);
        self::initDatabase();
        $entityManager = self::getEntityManager();

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
        });

        $this->assertThrownException(RuntimeException::class, 1);
        $events = self::getService(EventDispatcher::class)->getDispatchedEvents();
        self::assertCount(2, $events);
    }

    public function testEventIsDispatchedIfTimezoneWasChanged(): void
    {
        self::bootKernel(['environment' => 'category']);
        self::initDatabase();
        $entityManager = self::getEntityManager();
        $entityManager->getConnection()
            ->insert(
                'category',
                [
                    'id' => 1,
                    'name' => 'Computer',
                    'active_till' => '2022-12-20 16:23:52',
                    'created_at' => '2022-12-20 16:23:52',
                    'updated_at' => '2022-12-20 16:23:52',
                ]
            );
        /** @var \EonX\EasyDoctrine\Tests\Fixture\App\Entity\Category $category */
        $category = $entityManager->getRepository(Category::class)->find(1);
        /** @var \DateTime $activeTill */
        $activeTill = $category->getActiveTill();
        $newActiveTill = (clone $activeTill)->setTimezone(new DateTimeZone('Asia/Krasnoyarsk'));
        $category->setActiveTill($newActiveTill);

        $entityManager->persist($category);
        $entityManager->flush();

        $events = self::getService(EventDispatcher::class)->getDispatchedEvents();
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
        self::bootKernel(['environment' => 'category_product']);
        self::initDatabase();
        $entityManager = self::getEntityManager();
        $activeTill = '2022-12-20 16:23:52';
        $entityManager->getConnection()
            ->insert(
                'category',
                [
                    'id' => 1,
                    'name' => 'Computer',
                    'active_till' => $activeTill,
                    'created_at' => '2022-12-20 16:23:52',
                    'updated_at' => '2022-12-20 16:23:52',
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
        /** @var \EonX\EasyDoctrine\Tests\Fixture\App\Entity\Category $category */
        $category = $entityManager->getRepository(Category::class)->find(1);
        $category->setActiveTill(new DateTime($activeTill));
        /** @var \EonX\EasyDoctrine\Tests\Fixture\App\Entity\Product $product */
        $product = $entityManager->getRepository(Product::class)->find(1);
        $product->setPrice(new Price('1000', 'USD'));

        $entityManager->flush();

        $events = self::getService(EventDispatcher::class)->getDispatchedEvents();
        self::assertCount(0, $events);
    }

    public function testEventsAreDispatchedAfterEnablingDispatcher(): void
    {
        self::bootKernel(['environment' => 'product']);
        self::initDatabase();
        $entityManager = self::getEntityManager();
        $dispatcher = self::getService(DeferredEntityEventDispatcherInterface::class);

        $dispatcher->disable();
        $dispatcher->enable();
        $product = new Product();
        $product->setName('Description 1');
        $product->setPrice(new Price('1000', 'USD'));
        $entityManager->persist($product);

        $entityManager->flush();

        $events = self::getService(EventDispatcher::class)->getDispatchedEvents();
        self::assertCount(1, $events);
    }

    public function testEventsAreDispatchedWhenExceptionIsThrownAndCaught(): void
    {
        self::bootKernel(['environment' => 'product']);
        self::initDatabase();
        $entityManager = self::getEntityManager();
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

        $events = self::getService(EventDispatcher::class)->getDispatchedEvents();
        self::assertCount(2, $events);
        self::assertEqualsCanonicalizing(
            new EntityCreatedEvent(
                $product,
                [
                    'category' => [null, null],
                    'name' => [null, 'Description 1'],
                    'price' => [null, new Price('1000', 'USD')],
                ]
            ),
            $events[1]
        );
    }

    public function testEventsAreDispatchedWhenMultipleEntitiesAreChanged(): void
    {
        self::bootKernel(['environment' => 'category_product']);
        self::initDatabase();
        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);
        $entityManager = self::getEntityManager();
        $category = new Category();
        $category->setName('Computer');
        $entityManager->persist($category);
        $product = new Product();
        $product->setName('Keyboard');
        $price = new Price('1000', 'USD');
        $product->setPrice($price);
        $entityManager->persist($product);

        $entityManager->flush();

        $events = self::getService(EventDispatcher::class)->getDispatchedEvents();
        self::assertCount(2, $events);
        self::assertEqualsCanonicalizing(
            new EntityCreatedEvent(
                $category,
                [
                    'activeTill' => [null, null],
                    'name' => [null, 'Computer'],
                    'createdAt' => [null, $now],
                    'updatedAt' => [null, $now],
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
        self::bootKernel(['environment' => 'category_product']);
        self::initDatabase();
        $entityManager = self::getEntityManager();
        $entityManager->getConnection()
            ->insert(
                'category',
                [
                    'id' => 1,
                    'name' => 'Computer',
                    'created_at' => '2022-12-20 16:23:52',
                    'updated_at' => '2022-12-20 16:23:52',
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
        /** @var \EonX\EasyDoctrine\Tests\Fixture\App\Entity\Category $category */
        $category = $entityManager->getRepository(Category::class)->find(1);
        /** @var \EonX\EasyDoctrine\Tests\Fixture\App\Entity\Product $product */
        $product = $entityManager->getRepository(Product::class)->find(1);
        $category->setName('Computer Peripherals');
        $product->setPrice(new Price('2000', 'USD'));

        $entityManager->persist($category);
        $entityManager->persist($product);
        $entityManager->flush();

        $events = self::getService(EventDispatcher::class)->getDispatchedEvents();
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
        self::bootKernel(['environment' => 'category_product']);
        self::initDatabase();
        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);
        $entityManager = self::getEntityManager();
        $eventDispatcher = self::getService(EventDispatcher::class);
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
                    'createdAt' => [null, $now],
                    'updatedAt' => [null, $now],
                ]
            ),
            $events[0]
        );
        /** @var \EonX\EasyDoctrine\Tests\Fixture\App\Entity\Product $product */
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
        self::bootKernel(['environment' => 'product']);
        self::initDatabase();
        $entityManager = self::getEntityManager();
        $category = new Category();
        $category->setName('Computer');
        $entityManager->persist($category);
        $product = new Product();
        $product->setName('Keyboard');
        $product->setPrice(new Price('1000', 'USD'));
        $product->setCategory($category);
        $entityManager->persist($product);

        $entityManager->flush();

        $events = self::getService(EventDispatcher::class)->getDispatchedEvents();
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
        self::bootKernel(['environment' => 'product_tag']);
        self::initDatabase();
        $entityManager = self::getEntityManager();
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

        /** @var \EonX\EasyDoctrine\Tests\Fixture\App\Entity\Product $product */
        $product = $entityManager->getRepository(Product::class)->find(1);
        /** @var \EonX\EasyDoctrine\Tests\Fixture\App\Entity\Tag $tag2 */
        $tag2 = $entityManager->getRepository(Tag::class)->find(2);
        $product->getTags()
            ->toArray();
        $tag2->setName('New Tag 2 Name');
        $entityManager->flush();

        $events = self::getService(EventDispatcher::class)->getDispatchedEvents();
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
        self::bootKernel(['environment' => 'product']);
        self::initDatabase();
        $entityManager = self::getEntityManager();
        self::getService(DeferredEntityEventDispatcherInterface::class)->disable();
        $product = new Product();
        $product->setName('Description 1');
        $product->setPrice(new Price('1000', 'USD'));
        $entityManager->persist($product);
        $entityManager->flush();
        $product->setPrice(new Price('2000', 'USD'));
        $entityManager->flush();

        $events = self::getService(EventDispatcher::class)->getDispatchedEvents();
        self::assertCount(0, $events);
    }

    public function testEventsAreNotDispatchedWhenNoChangesMade(): void
    {
        self::bootKernel(['environment' => 'product']);
        self::initDatabase();
        $entityManager = self::getEntityManager();

        $entityManager->flush();

        $events = self::getService(EventDispatcher::class)->getDispatchedEvents();
        self::assertCount(0, $events);
    }

    public function testEventsAreNotDispatchedWhenRelatedEntitiesAreChanged(): void
    {
        self::bootKernel(['environment' => 'product']);
        self::initDatabase();
        $entityManager = self::getEntityManager();
        $entityManager->getConnection()
            ->insert(
                'category',
                [
                    'id' => 1,
                    'name' => 'Computer',
                    'created_at' => '2022-12-20 16:23:52',
                    'updated_at' => '2022-12-20 16:23:52',
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

        /** @var \EonX\EasyDoctrine\Tests\Fixture\App\Entity\Product $product */
        $product = $entityManager->getRepository(Product::class)->find(1);
        /** @var \EonX\EasyDoctrine\Tests\Fixture\App\Entity\Category $category */
        $category = $product->getCategory();
        $category->setName('Computer Peripherals');

        $entityManager->flush();

        $events = self::getService(EventDispatcher::class)->getDispatchedEvents();
        self::assertCount(0, $events);
    }

    public function testEventsAreNotDispatchedWhenSubscriptionDoesNotExist(): void
    {
        self::initDatabase();
        $entityManager = self::getEntityManager();
        $product = new Product();
        $product->setName('Product 1');
        $product->setPrice(new Price('1000', 'USD'));
        $entityManager->persist($product);

        $entityManager->flush();

        $events = self::getService(EventDispatcher::class)->getDispatchedEvents();
        self::assertCount(0, $events);
    }

    public function testOneEventIsDispatchedForDeletedEntity(): void
    {
        self::bootKernel(['environment' => 'product']);
        self::initDatabase();
        $entityManager = self::getEntityManager();
        $entityManager->getConnection()
            ->insert(
                'category',
                [
                    'id' => 1,
                    'name' => 'Computer',
                    'created_at' => '2022-12-20 16:23:52',
                    'updated_at' => '2022-12-20 16:23:52',
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

        /** @var \EonX\EasyDoctrine\Tests\Fixture\App\Entity\Product $product */
        $product = $entityManager->getRepository(Product::class)->find(1);

        $entityManager->remove($product);
        $entityManager->flush();

        $events = self::getService(EventDispatcher::class)->getDispatchedEvents();
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
        /** @var \EonX\EasyDoctrine\Tests\Fixture\App\Entity\Product $product */
        $product = $actualEvent->getEntity();
        self::assertInstanceOf(Product::class, $product);
        self::assertSame(1, $product->getId());
        self::assertSame('Keyboard', $product->getName());
        self::assertEquals(new Price('1000', 'USD'), $product->getPrice());
        self::assertNotNull($product->getCategory());
        /** @var \EonX\EasyDoctrine\Tests\Fixture\App\Entity\Category $category */
        $category = $product->getCategory();
        self::assertSame(1, $category->getId());
        self::assertSame('Computer', $category->getName());
        /** @var \EonX\EasyDoctrine\Tests\Fixture\App\Entity\Tag $tag1 */
        $tag1 = $product->getTags()[0];
        self::assertSame(1, $tag1->getId());
        self::assertSame('Tag 1', $tag1->getName());
        /** @var \EonX\EasyDoctrine\Tests\Fixture\App\Entity\Tag $tag2 */
        $tag2 = $product->getTags()[1];
        self::assertSame(2, $tag2->getId());
        self::assertSame('Tag 2', $tag2->getName());
    }

    public function testOneEventIsDispatchedForMultipleUpdatedEntity(): void
    {
        self::bootKernel(['environment' => 'product']);
        self::initDatabase();
        $entityManager = self::getEntityManager();
        $entityManager->getConnection()
            ->insert(
                'product',
                [
                    'id' => 1,
                    'name' => 'Keyboard',
                    'price' => '1000 USD',
                ]
            );
        /** @var \EonX\EasyDoctrine\Tests\Fixture\App\Entity\Product $product */
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

        $events = self::getService(EventDispatcher::class)->getDispatchedEvents();
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
        self::bootKernel(['environment' => 'product']);
        self::initDatabase();
        $entityManager = self::getEntityManager();
        $product = new Product();
        $entityManager->wrapInTransaction(function () use ($entityManager, $product): void {
            $product->setName('Name 1');
            $product->setPrice(new Price('1000', 'USD'));
            $entityManager->persist($product);
            $entityManager->flush();
            $entityManager->wrapInTransaction(function () use ($entityManager, $product): void {
                $product->setPrice(new Price('2000', 'USD'));
                $entityManager->flush();
            });
        });
        $entityManager->flush();

        $events = self::getService(EventDispatcher::class)->getDispatchedEvents();
        self::assertCount(1, $events);
        self::assertEqualsCanonicalizing(
            new EntityCreatedEvent(
                $product,
                [
                    'name' => [null, 'Name 1'],
                    'price' => [null, new Price('2000', 'USD')],
                    'category' => [null, null],
                ]
            ),
            $events[0]
        );
    }

    public function testOneEventIsDispatchedForNewEntityCreatedAndUpdatedInTransaction(): void
    {
        self::bootKernel(['environment' => 'product']);
        self::initDatabase();
        $entityManager = self::getEntityManager();
        $product = new Product();
        $product->setName('Name 1');
        $product->setPrice(new Price('1000', 'USD'));

        $entityManager->persist($product);
        $entityManager->wrapInTransaction(function () use ($entityManager, $product): void {
            $product->setName('Name 2');

            $entityManager->flush();

            $product->setName('Name 3');

            $entityManager->flush();

            $product->setPrice(new Price('2000', 'AUD'));
        });

        $events = self::getService(EventDispatcher::class)->getDispatchedEvents();
        self::assertCount(1, $events);
        self::assertEqualsCanonicalizing(
            new EntityCreatedEvent(
                $product,
                [
                    'name' => [null, 'Name 3'],
                    'price' => [null, new Price('2000', 'AUD')],
                    'category' => [null, null],
                ]
            ),
            $events[0]
        );
    }

    public function testOneEventIsDispatchedForUpdatedCollection(): void
    {
        self::bootKernel(['environment' => 'product']);
        self::initDatabase();
        $entityManager = self::getEntityManager();
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

        /** @var \EonX\EasyDoctrine\Tests\Fixture\App\Entity\Product $product */
        $product = $entityManager->getRepository(Product::class)->find(1);
        /** @var \EonX\EasyDoctrine\Tests\Fixture\App\Entity\Tag $tag2 */
        $tag2 = $entityManager->getRepository(Tag::class)->find(2);
        $product->getTags()
            ->toArray();
        $entityManager->remove($tag2);
        $tag3 = (new Tag())
            ->setName('Tag 3');
        $product->addTag($tag3);
        $entityManager->flush();

        $events = self::getService(EventDispatcher::class)->getDispatchedEvents();
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
        self::bootKernel(['environment' => 'product']);
        self::initDatabase();
        $entityManager = self::getEntityManager();
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

        /** @var \EonX\EasyDoctrine\Tests\Fixture\App\Entity\Product $product */
        $product = $entityManager->getRepository(Product::class)->find(1);
        $product->getOffers()
            ->clear();
        $entityManager->flush();

        $events = self::getService(EventDispatcher::class)->getDispatchedEvents();
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
        self::bootKernel(['environment' => 'product']);
        self::initDatabase();
        $entityManager = self::getEntityManager();
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

        /** @var \EonX\EasyDoctrine\Tests\Fixture\App\Entity\Product $product */
        $product = $entityManager->getRepository(Product::class)->find(1);
        /** @var \EonX\EasyDoctrine\Tests\Fixture\App\Entity\Tag $tag2 */
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

        $events = self::getService(EventDispatcher::class)->getDispatchedEvents();
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
