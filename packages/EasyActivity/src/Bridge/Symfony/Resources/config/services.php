<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\ActivityLogEntryFactory;
use EonX\EasyActivity\Bridge\BridgeConstantsInterface;
use EonX\EasyActivity\Bridge\Symfony\Messenger\ActivityLogEntryMessageHandler;
use EonX\EasyActivity\Bridge\Symfony\Messenger\AsyncDispatcher;
use EonX\EasyActivity\Bridge\Symfony\Normalizers\SymfonyNormalizer;
use EonX\EasyActivity\DefaultActorResolver;
use EonX\EasyActivity\Interfaces\ActivityLogEntryFactoryInterface;
use EonX\EasyActivity\Interfaces\ActorResolverInterface;
use EonX\EasyActivity\Interfaces\AsyncDispatcherInterface;
use EonX\EasyActivity\Interfaces\NormalizerInterface;
use EonX\EasyActivity\Interfaces\StoreInterface;
use EonX\EasyActivity\Stores\DoctrineDbalStore;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(StoreInterface::class, DoctrineDbalStore::class)
        ->arg('$table', 'easy_activity_logs');

    $services
        ->set(ActorResolverInterface::class, DefaultActorResolver::class);

    $services
        ->set(NormalizerInterface::class, SymfonyNormalizer::class);

    $services
        ->set(ActivityLogEntryFactoryInterface::class, ActivityLogEntryFactory::class)
        ->arg('$disallowedProperties', '%' . BridgeConstantsInterface::PARAM_DISALLOWED_PROPERTIES . '%')
        ->arg('$subjects', '%' . BridgeConstantsInterface::PARAM_SUBJECTS . '%');

    $services
        ->set(ActivityLogEntryMessageHandler::class);

    $services
        ->set(AsyncDispatcherInterface::class, AsyncDispatcher::class);
};
