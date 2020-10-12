<?php

declare(strict_types=1);

namespace EonX\EasyEntityChange\Tests\Integration\TestCases\Integration;

use EonX\EasyEntityChange\DataTransferObjects\UpdatedEntity;
use EonX\EasyEntityChange\Doctrine\EntityChangeSubscriber;
use EonX\EasyEntityChange\Events\EntityChangeEvent;
use EonX\EasyEntityChange\Tests\Integration\Fixtures\ProvidedIdEntity;
use EonX\EasyEntityChange\Tests\Integration\Fixtures\SimpleEntity;
use EonX\EasyEntityChange\Tests\Stubs\EventDispatcherStub;
use Eonx\TestUtils\TestCases\Integration\DoctrineORMTestCase;

/**
 * @coversNothing
 */
final class DoctrineORMTestCaseTest extends DoctrineORMTestCase
{
    public function testEntityChangeEventIsDispatchedWithDbId(): void
    {
        $expectedDispatches = [
            new EntityChangeEvent([
                new UpdatedEntity(['property'], SimpleEntity::class, [
                    'id' => 1,
                ]),
            ]),
        ];

        $dispatcher = new EventDispatcherStub();

        $entityManager = $this->getEntityManager();
        $entityManager->getEventManager()
            ->addEventSubscriber(new EntityChangeSubscriber($dispatcher));

        $entity = new SimpleEntity();
        $entity->setProperty('hello');

        $entityManager->persist($entity);
        $entityManager->flush();

        self::assertEquals($expectedDispatches, $dispatcher->getDispatched());
    }

    public function testEntityChangeEventIsDispatchedWithProvidedId(): void
    {
        $expectedDispatches = [
            new EntityChangeEvent([
                new UpdatedEntity(['id', 'property'], ProvidedIdEntity::class, [
                    'id' => 'uuid',
                ]),
            ]),
        ];

        $dispatcher = new EventDispatcherStub();

        $entityManager = $this->getEntityManager();
        $entityManager->getEventManager()
            ->addEventSubscriber(new EntityChangeSubscriber($dispatcher));

        $entity = new ProvidedIdEntity('uuid');
        $entity->setProperty('hello');

        $entityManager->persist($entity);
        $entityManager->flush();

        self::assertEquals($expectedDispatches, $dispatcher->getDispatched());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->markTestSkipped('Skip until fix utils');
    }
}
