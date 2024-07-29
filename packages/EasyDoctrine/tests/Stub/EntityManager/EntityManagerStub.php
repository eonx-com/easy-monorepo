<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Stub\EntityManager;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Tools\SchemaTool;
use EonX\EasyDoctrine\Bundle\Factory\ObjectCopierFactory;
use EonX\EasyDoctrine\EntityEvent\Dispatcher\DeferredEntityEventDispatcher;
use EonX\EasyDoctrine\EntityEvent\EntityManager\WithEventsEntityManager;
use EonX\EasyDoctrine\EntityEvent\Listener\EntityEventListener;
use EonX\EasyDoctrine\Tests\Fixture\Type\PriceType;
use EonX\EasyEventDispatcher\Dispatcher\EventDispatcher;
use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;

final class EntityManagerStub
{
    /**
     * @param class-string[] $trackableEntities
     * @param string[] $fixtures
     *
     * @return \EonX\EasyDoctrine\EntityEvent\EntityManager\WithEventsEntityManager
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public static function createFromDeferredEntityEventDispatcher(
        DeferredEntityEventDispatcher $dispatcher,
        array $trackableEntities = [],
        array $fixtures = [],
    ) {
        $eventListener = new EntityEventListener($dispatcher, $trackableEntities);
        $eventManager = new EventManager();
        $eventManager->addEventListener([Events::onFlush, Events::postFlush], $eventListener);
        $entityManagerStub = self::createFromEventManager($eventManager, $fixtures);
        $eventDispatcher = new EventDispatcher(new SymfonyEventDispatcher());

        return new WithEventsEntityManager(
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

        if (Type::hasType(PriceType::PRICE) === false) {
            Type::addType(PriceType::PRICE, PriceType::class);
        }

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema([]);
        $schemaTool->createSchema($schema);

        $entityManager->getConnection()
            ->setNestTransactionsWithSavepoints(true);

        return $entityManager;
    }

    /**
     * @param class-string[] $trackableEntities
     * @param string[] $fixtures
     *
     * @return \EonX\EasyDoctrine\EntityEvent\EntityManager\WithEventsEntityManager
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public static function createFromSymfonyEventDispatcher(
        EventDispatcherInterface $eventDispatcher,
        array $trackableEntities = [],
        array $fixtures = [],
    ) {
        $dispatcher = new DeferredEntityEventDispatcher($eventDispatcher, ObjectCopierFactory::create());

        return self::createFromDeferredEntityEventDispatcher(
            $dispatcher,
            $trackableEntities,
            $fixtures
        );
    }
}
