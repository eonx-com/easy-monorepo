<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Stubs;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Tools\SchemaTool;
use EonX\EasyDoctrine\Bridge\Symfony\DependencyInjection\Factory\ObjectCopierFactory;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcher;
use EonX\EasyDoctrine\Listeners\EntityEventListener;
use EonX\EasyDoctrine\ORM\Decorators\EntityManagerDecorator;
use EonX\EasyDoctrine\Subscribers\EntityEventSubscriber;
use EonX\EasyDoctrine\Tests\Fixtures\PriceType;
use EonX\EasyEventDispatcher\Bridge\Symfony\EventDispatcher;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;

final class EntityManagerStub
{
    /**
     * @param class-string[] $subscribedEntities
     * @param string[] $fixtures
     *
     * @return \EonX\EasyDoctrine\ORM\Decorators\EntityManagerDecorator
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public static function createFromDeferredEntityEventDispatcher(
        DeferredEntityEventDispatcher $dispatcher,
        array $subscribedEntities = [],
        array $fixtures = [],
    ) {
        $eventListener = new EntityEventListener($dispatcher);
        $eventSubscriber = new EntityEventSubscriber($eventListener, $subscribedEntities);
        $eventManager = new EventManager();
        $eventManager->addEventSubscriber($eventSubscriber);
        $entityManagerStub = self::createFromEventManager($eventManager, $fixtures);
        $eventDispatcher = new EventDispatcher(new SymfonyEventDispatcher());

        return new EntityManagerDecorator(
            $dispatcher,
            $eventDispatcher,
            $entityManagerStub
        );
    }

    /**
     * @param string[] $fixtures
     *
     * @return \Doctrine\ORM\EntityManager
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public static function createFromEventManager(?EventManager $eventManager = null, array $fixtures = [])
    {
        $conn = [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ];

        $config = new Configuration();
        $config->setProxyDir(__DIR__ . '/../var');
        $config->setProxyNamespace('Proxy');

        $config->setMetadataDriverImpl(new AttributeDriver([]));

        $entityManager = EntityManager::create($conn, $config, $eventManager);
        $schema = \array_map(fn ($class): ClassMetadata => $entityManager->getClassMetadata($class), $fixtures);

        if (Type::hasType(PriceType::NAME) === false) {
            Type::addType(PriceType::NAME, PriceType::class);
        }

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema([]);
        $schemaTool->createSchema($schema);

        $entityManager->getConnection()
            ->setNestTransactionsWithSavepoints(true);

        return $entityManager;
    }

    /**
     * @param class-string[] $subscribedEntities
     * @param string[] $fixtures
     *
     * @return \EonX\EasyDoctrine\ORM\Decorators\EntityManagerDecorator
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public static function createFromSymfonyEventDispatcher(
        EventDispatcherInterface $eventDispatcher,
        array $subscribedEntities = [],
        array $fixtures = [],
    ) {
        $dispatcher = new DeferredEntityEventDispatcher($eventDispatcher, ObjectCopierFactory::create());

        return self::createFromDeferredEntityEventDispatcher(
            $dispatcher,
            $subscribedEntities,
            $fixtures
        );
    }
}
