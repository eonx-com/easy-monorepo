<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Bundle\Enum\ConfigParam;
use EonX\EasyActivity\Bundle\Enum\ConfigServiceId;
use EonX\EasyActivity\Common\CircularReferenceHandler\CircularReferenceHandler;
use EonX\EasyActivity\Common\Dispatcher\AsyncDispatcherInterface;
use EonX\EasyActivity\Common\Factory\ActivityLogEntryFactory;
use EonX\EasyActivity\Common\Factory\ActivityLogEntryFactoryInterface;
use EonX\EasyActivity\Common\Factory\IdFactoryInterface;
use EonX\EasyActivity\Common\Factory\UuidFactory;
use EonX\EasyActivity\Common\Logger\ActivityLoggerInterface;
use EonX\EasyActivity\Common\Logger\AsyncActivityLogger;
use EonX\EasyActivity\Common\Resolver\ActivitySubjectDataResolverInterface;
use EonX\EasyActivity\Common\Resolver\ActivitySubjectResolverInterface;
use EonX\EasyActivity\Common\Resolver\ActorResolverInterface;
use EonX\EasyActivity\Common\Resolver\DefaultActivitySubjectResolver;
use EonX\EasyActivity\Common\Resolver\DefaultActorResolver;
use EonX\EasyActivity\Common\Serializer\ActivitySubjectDataSerializerInterface;
use EonX\EasyActivity\Common\Serializer\SymfonyActivitySubjectDataSerializer;
use EonX\EasyActivity\Common\Store\StoreInterface;
use EonX\EasyActivity\Doctrine\Resolver\DoctrineActivitySubjectDataResolver;
use EonX\EasyActivity\Doctrine\Store\DoctrineDbalStore;
use EonX\EasyActivity\Messenger\Dispatcher\AsyncDispatcher;
use EonX\EasyActivity\Messenger\MessageHandler\ActivityLogEntryMessageHandler;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(StoreInterface::class, DoctrineDbalStore::class)
        ->arg('$table', param(ConfigParam::TableName->value));

    $services
        ->set(IdFactoryInterface::class, UuidFactory::class);

    $services
        ->set(ActorResolverInterface::class, DefaultActorResolver::class);

    $services
        ->set(ActivitySubjectResolverInterface::class, DefaultActivitySubjectResolver::class)
        ->arg('$subjects', param(ConfigParam::Subjects->value));

    $services
        ->set(ActivitySubjectDataResolverInterface::class, DoctrineActivitySubjectDataResolver::class);

    $services
        ->set(ActivityLoggerInterface::class, AsyncActivityLogger::class);

    $services
        ->alias(ConfigServiceId::Serializer->value, 'serializer');

    $services->set(ConfigServiceId::CircularReferenceHandler->value, CircularReferenceHandler::class);

    $services
        ->set(ActivitySubjectDataSerializerInterface::class, SymfonyActivitySubjectDataSerializer::class)
        ->arg('$serializer', service(ConfigServiceId::Serializer->value))
        ->arg('$circularReferenceHandler', service(ConfigServiceId::CircularReferenceHandler->value))
        ->arg('$disallowedProperties', param(ConfigParam::DisallowedProperties->value));

    $services
        ->set(ActivityLogEntryFactoryInterface::class, ActivityLogEntryFactory::class);

    $services
        ->set(ActivityLogEntryMessageHandler::class);

    $services
        ->set(AsyncDispatcherInterface::class, AsyncDispatcher::class);
};
