<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\ActivityLogEntryFactory;
use EonX\EasyActivity\Bridge\BridgeConstantsInterface;
use EonX\EasyActivity\Bridge\Doctrine\DoctrineDbalStore;
use EonX\EasyActivity\Bridge\Doctrine\DoctrineSubjectDataResolver;
use EonX\EasyActivity\Bridge\Symfony\Messenger\ActivityLogEntryMessageHandler;
use EonX\EasyActivity\Bridge\Symfony\Messenger\AsyncDispatcher;
use EonX\EasyActivity\Bridge\Symfony\Serializers\SymfonySerializer;
use EonX\EasyActivity\DefaultActorResolver;
use EonX\EasyActivity\DefaultSubjectResolver;
use EonX\EasyActivity\Interfaces\ActivityLogEntryFactoryInterface;
use EonX\EasyActivity\Interfaces\ActorResolverInterface;
use EonX\EasyActivity\Interfaces\AsyncDispatcherInterface;
use EonX\EasyActivity\Interfaces\SerializerInterface;
use EonX\EasyActivity\Interfaces\StoreInterface;
use EonX\EasyActivity\Interfaces\SubjectDataResolverInterface;
use EonX\EasyActivity\Interfaces\SubjectResolverInterface;

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
        ->set(SubjectResolverInterface::class, DefaultSubjectResolver::class)
        ->arg('$subjects', '%' . BridgeConstantsInterface::PARAM_SUBJECTS . '%');

    $services
        ->set(SubjectDataResolverInterface::class, DoctrineSubjectDataResolver::class);

    $services
        ->set(SerializerInterface::class, SymfonySerializer::class)
        ->arg('$disallowedProperties', '%' . BridgeConstantsInterface::PARAM_DISALLOWED_PROPERTIES . '%');

    $services
        ->set(ActivityLogEntryFactoryInterface::class, ActivityLogEntryFactory::class);

    $services
        ->set(ActivityLogEntryMessageHandler::class);

    $services
        ->set(AsyncDispatcherInterface::class, AsyncDispatcher::class);
};
