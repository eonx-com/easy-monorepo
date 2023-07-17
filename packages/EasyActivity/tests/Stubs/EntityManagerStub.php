<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Stubs;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Tools\SchemaTool;
use EonX\EasyActivity\Bridge\Doctrine\DoctrineDbalStatementsProvider;
use EonX\EasyActivity\Bridge\Doctrine\DoctrineDbalStore;
use EonX\EasyActivity\Bridge\EasyDoctrine\EasyDoctrineEntityEventsSubscriber;
use EonX\EasyActivity\Bridge\Symfony\Messenger\ActivityLogEntryMessage;
use EonX\EasyActivity\Bridge\Symfony\Messenger\ActivityLogEntryMessageHandler;
use EonX\EasyActivity\Bridge\Symfony\Messenger\AsyncDispatcher;
use EonX\EasyActivity\Bridge\Symfony\Uid\UuidFactory;
use EonX\EasyActivity\Interfaces\ActivitySubjectResolverInterface;
use EonX\EasyActivity\Interfaces\ActorResolverInterface;
use EonX\EasyActivity\Logger\AsyncActivityLogger;
use EonX\EasyActivity\Tests\Fixtures\Article;
use EonX\EasyActivity\Tests\Fixtures\Author;
use EonX\EasyActivity\Tests\Fixtures\Comment;
use EonX\EasyDoctrine\Bridge\Symfony\DependencyInjection\Factory\ObjectCopierFactory;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcher;
use EonX\EasyDoctrine\ORM\Decorators\EntityManagerDecorator;
use EonX\EasyDoctrine\Subscribers\EntityEventSubscriber;
use EonX\EasyEventDispatcher\Bridge\Symfony\EventDispatcher;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Uid\Factory\UuidFactory as SymfonyUuidFactory;

final class EntityManagerStub
{
    public const ACTIVITY_TABLE_NAME = 'test_easy_activity_logs';

    /**
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
        array $fixtures = [],
    ) {
        $eventSubscriber = new EntityEventSubscriber($dispatcher, $subscribedEntities);
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
     * @param array<string, mixed> $easyActivityConfig
     * @param string[]|null $fixtures
     *
     * @return \Doctrine\ORM\EntityManagerInterface
     */
    public static function createFromEasyActivityConfig(
        array $easyActivityConfig,
        ?ActorResolverInterface $actorResolver = null,
        ?ActivitySubjectResolverInterface $subjectResolver = null,
        ?array $fixtures = null,
    ): EntityManagerInterface {
        $symfonyEventDispatcher = new SymfonyEventDispatcher();
        $eventDispatcher = new EventDispatcher($symfonyEventDispatcher);
        /** @var string[] $subscribedEntities */
        $subscribedEntities = \array_keys($easyActivityConfig['subjects'] ?? []);
        $entityManager = self::createFromSymfonyEventDispatcher(
            $eventDispatcher,
            $subscribedEntities,
            $fixtures ?? [Article::class, Comment::class, Author::class]
        );
        $dbalStatementsProvider = new DoctrineDbalStatementsProvider(
            $entityManager->getConnection(),
            self::ACTIVITY_TABLE_NAME
        );
        foreach ($dbalStatementsProvider->migrateStatements() as $migrateStatement) {
            $entityManager->getConnection()
                ->executeQuery($migrateStatement);
        }
        $uuidFactory = new UuidFactory(new SymfonyUuidFactory());
        $dbalStore = new DoctrineDbalStore(
            $uuidFactory,
            $entityManager->getConnection(),
            self::ACTIVITY_TABLE_NAME
        );

        $activityLogEntryFactory = new ActivityLogFactoryStub(
            $easyActivityConfig['subjects'] ?? null,
            $easyActivityConfig['disallowed_properties'] ?? [],
            $actorResolver,
            $subjectResolver
        );

        $messageBus = new MessageBus([
            new HandleMessageMiddleware(new HandlersLocator([
                ActivityLogEntryMessage::class => [new ActivityLogEntryMessageHandler($dbalStore)],
            ])),
        ]);
        $asyncDispatcher = new AsyncDispatcher($messageBus);
        $subscriber = new EasyDoctrineEntityEventsSubscriber(
            new AsyncActivityLogger($activityLogEntryFactory, $asyncDispatcher),
            true
        );

        $symfonyEventDispatcher->addSubscriber($subscriber);

        return $entityManager;
    }

    /**
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

        $config->setMetadataDriverImpl(new AttributeDriver([]));

        $entityManager = EntityManager::create($conn, $config, $eventManager);
        $schema = \array_map(fn ($class): ClassMetadata => $entityManager->getClassMetadata($class), $fixtures);

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema([]);
        $schemaTool->createSchema($schema);

        $entityManager->getConnection()
            ->setNestTransactionsWithSavepoints(true);

        return $entityManager;
    }

    /**
     * @param string[] $subscribedEntities
     * @param string[] $fixtures
     *
     * @return \EonX\EasyDoctrine\ORM\Decorators\EntityManagerDecorator
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
