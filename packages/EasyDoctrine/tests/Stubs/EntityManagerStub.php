<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Stubs;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\SchemaTool;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcher;
use EonX\EasyDoctrine\ORM\Decorators\EntityManagerDecorator;
use EonX\EasyDoctrine\Subscribers\EntityEventSubscriber;
use EonX\EasyErrorHandler\ErrorHandler;
use EonX\EasyErrorHandler\Response\ErrorResponseFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class EntityManagerStub extends EntityManager
{
    /**
     * @param \EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcher $dispatcher
     * @param string[] $subscribedEntities
     * @param string[] $fixtures
     *
     * @return \EonX\EasyDoctrine\ORM\Decorators\EntityManagerDecorator
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public static function createFromDeferredEntityEventDispatcher(
        DeferredEntityEventDispatcher $dispatcher,
        array $subscribedEntities = [],
        array $fixtures = []
    ) {
        $eventSubscriber = new EntityEventSubscriber($dispatcher, $subscribedEntities);
        $eventManager = new EventManager();
        $eventManager->addEventSubscriber($eventSubscriber);
        $entityManagerStub = self::createFromEventManager($eventManager, $fixtures);
        $errorHandler = new ErrorHandler(new ErrorResponseFactory(), [], []);

        return new EntityManagerDecorator(
            $dispatcher,
            $errorHandler,
            $entityManagerStub
        );
    }

    /**
     * @param \Doctrine\Common\EventManager|null $eventManager
     * @param string[] $fixtures
     *
     * @return \Doctrine\ORM\EntityManager
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

        $config->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader()));

        $entityManager = parent::create($conn, $config, $eventManager);
        $schema = \array_map(function ($class) use ($entityManager) {
            return $entityManager->getClassMetadata($class);
        }, $fixtures);

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema([]);
        $schemaTool->createSchema($schema);

        $entityManager->getConnection()
            ->setNestTransactionsWithSavepoints(true);

        return $entityManager;
    }

    /**
     * @param \Symfony\Component\EventDispatcher\EventDispatcher $eventDispatcher
     * @param string[] $subscribedEntities
     * @param string[] $fixtures
     *
     * @return \EonX\EasyDoctrine\ORM\Decorators\EntityManagerDecorator
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public static function createFromSymfonyEventDispatcher(
        EventDispatcher $eventDispatcher,
        array $subscribedEntities = [],
        array $fixtures = []
    ) {
        $dispatcher = new DeferredEntityEventDispatcher($eventDispatcher);

        return self::createFromDeferredEntityEventDispatcher(
            $dispatcher,
            $subscribedEntities,
            $fixtures
        );
    }
}
