<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\ActivityLogEntryFactory;
use EonX\EasyActivity\Bridge\BridgeConstantsInterface;
use EonX\EasyActivity\Bridge\Doctrine\DoctrineActivitySubjectDataResolver;
use EonX\EasyActivity\Bridge\Doctrine\DoctrineDbalStore;
use EonX\EasyActivity\Bridge\Symfony\Messenger\ActivityLogEntryMessageHandler;
use EonX\EasyActivity\Bridge\Symfony\Messenger\AsyncDispatcher;
use EonX\EasyActivity\Bridge\Symfony\Serializers\SymfonyActivitySubjectDataSerializer;
use EonX\EasyActivity\Interfaces\ActivityLogEntryFactoryInterface;
use EonX\EasyActivity\Interfaces\ActivityLoggerInterface;
use EonX\EasyActivity\Interfaces\ActivitySubjectDataResolverInterface;
use EonX\EasyActivity\Interfaces\ActivitySubjectDataSerializerInterface;
use EonX\EasyActivity\Interfaces\ActivitySubjectResolverInterface;
use EonX\EasyActivity\Interfaces\ActorResolverInterface;
use EonX\EasyActivity\Interfaces\AsyncDispatcherInterface;
use EonX\EasyActivity\Interfaces\StoreInterface;
use EonX\EasyActivity\Logger\AsyncActivityLogger;
use EonX\EasyActivity\Resolvers\DefaultActivitySubjectResolver;
use EonX\EasyActivity\Resolvers\DefaultActorResolver;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(StoreInterface::class, DoctrineDbalStore::class)
        ->arg('$table', '%' . BridgeConstantsInterface::PARAM_TABLE_NAME . '%');

    $services
        ->set(ActorResolverInterface::class, DefaultActorResolver::class);

    $services
        ->set(ActivitySubjectResolverInterface::class, DefaultActivitySubjectResolver::class)
        ->arg('$subjects', '%' . BridgeConstantsInterface::PARAM_SUBJECTS . '%');

    $services
        ->set(ActivitySubjectDataResolverInterface::class, DoctrineActivitySubjectDataResolver::class);

    $services
        ->set(ActivityLoggerInterface::class, AsyncActivityLogger::class);

    $services
        ->alias(BridgeConstantsInterface::SERVICE_SERIALIZER, 'serializer');

    $services
        ->set(ActivitySubjectDataSerializerInterface::class, SymfonyActivitySubjectDataSerializer::class)
        ->arg('$disallowedProperties', '%' . BridgeConstantsInterface::PARAM_DISALLOWED_PROPERTIES . '%')
        ->arg('$serializer', service(BridgeConstantsInterface::SERVICE_SERIALIZER));

    $services
        ->set(ActivityLogEntryFactoryInterface::class, ActivityLogEntryFactory::class);

    $services
        ->set(ActivityLogEntryMessageHandler::class);

    $services
        ->set(AsyncDispatcherInterface::class, AsyncDispatcher::class);
};
