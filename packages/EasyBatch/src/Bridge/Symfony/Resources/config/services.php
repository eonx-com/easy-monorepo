<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyBatch\BatchManager;
use EonX\EasyBatch\Bridge\BridgeConstantsInterface;
use EonX\EasyBatch\Bridge\Symfony\Messenger\AsyncDispatcher;
use EonX\EasyBatch\Bridge\Symfony\Messenger\DispatchBatchMiddleware;
use EonX\EasyBatch\Bridge\Symfony\Messenger\ProcessBatchItemMiddleware;
use EonX\EasyBatch\Bridge\Symfony\Serializers\MessageSerializerDecorator;
use EonX\EasyBatch\Factories\BatchFactory;
use EonX\EasyBatch\Factories\BatchItemFactory;
use EonX\EasyBatch\IdStrategies\UuidV4Strategy;
use EonX\EasyBatch\Interfaces\AsyncDispatcherInterface;
use EonX\EasyBatch\Interfaces\BatchFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchItemFactoryInterface;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Interfaces\BatchManagerInterface;
use EonX\EasyBatch\Interfaces\BatchObjectIdStrategyInterface;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;
use EonX\EasyBatch\Repositories\BatchItemRepository;
use EonX\EasyBatch\Repositories\BatchRepository;
use EonX\EasyBatch\Serializers\MessageSerializer;
use EonX\EasyBatch\Transformers\BatchItemTransformer;
use EonX\EasyBatch\Transformers\BatchTransformer;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind('$datetimeFormat', '%' . BridgeConstantsInterface::PARAM_DATE_TIME_FORMAT . '%')
        ->bind('$eventDispatcher', service(EventDispatcherInterface::class));

    // AsyncDispatcher
    $services->set(AsyncDispatcherInterface::class, AsyncDispatcher::class);

    // Factories
    $services
        ->set(BatchFactoryInterface::class, BatchFactory::class)
        ->arg('$transformer', service(BridgeConstantsInterface::SERVICE_BATCH_TRANSFORMER));

    $services
        ->set(BatchItemFactoryInterface::class, BatchItemFactory::class)
        ->arg('$transformer', service(BridgeConstantsInterface::SERVICE_BATCH_ITEM_TRANSFORMER));

    // IdStrategies
    $services
        ->set(BatchObjectIdStrategyInterface::class, UuidV4Strategy::class)
        ->alias(BridgeConstantsInterface::SERVICE_BATCH_ID_STRATEGY, BatchObjectIdStrategyInterface::class)
        ->alias(BridgeConstantsInterface::SERVICE_BATCH_ITEM_ID_STRATEGY, BatchObjectIdStrategyInterface::class);

    // Listeners
    foreach (BridgeConstantsInterface::LISTENERS as $listener) {
        $services
            ->set($listener)
            ->tag('kernel.event_listener');
    }

    // Manager
    $services->set(BatchManagerInterface::class, BatchManager::class);

    // Middleware
    $services->set(DispatchBatchMiddleware::class);
    $services->set(ProcessBatchItemMiddleware::class);

    // Repositories
    $services
        ->set(BatchRepositoryInterface::class, BatchRepository::class)
        ->arg('$factory', service(BatchFactoryInterface::class))
        ->arg('$idStrategy', service(BridgeConstantsInterface::SERVICE_BATCH_ID_STRATEGY))
        ->arg('$table', '%' . BridgeConstantsInterface::PARAM_BATCH_TABLE . '%')
        ->arg('$transformer', service(BridgeConstantsInterface::SERVICE_BATCH_TRANSFORMER));

    $services
        ->set(BatchItemRepositoryInterface::class, BatchItemRepository::class)
        ->arg('$factory', service(BatchItemFactoryInterface::class))
        ->arg('$idStrategy', service(BridgeConstantsInterface::SERVICE_BATCH_ITEM_ID_STRATEGY))
        ->arg('$table', '%' . BridgeConstantsInterface::PARAM_BATCH_ITEM_TABLE . '%')
        ->arg('$transformer', service(BridgeConstantsInterface::SERVICE_BATCH_ITEM_TRANSFORMER));

    //Serializer
    $services->set(BridgeConstantsInterface::SERVICE_BATCH_MESSAGE_SERIALIZER, MessageSerializer::class);

    $services->set(MessageSerializerDecorator::class)
        ->decorate(BridgeConstantsInterface::SERVICE_BATCH_MESSAGE_SERIALIZER)
        ->args([service('.inner')]);

    // Transformers
    $services
        ->set(BridgeConstantsInterface::SERVICE_BATCH_TRANSFORMER, BatchTransformer::class)
        ->arg('$messageSerializer', service(BridgeConstantsInterface::SERVICE_BATCH_MESSAGE_SERIALIZER))
        ->arg('$class', '%' . BridgeConstantsInterface::PARAM_BATCH_CLASS . '%');

    $services
        ->set(BridgeConstantsInterface::SERVICE_BATCH_ITEM_TRANSFORMER, BatchItemTransformer::class)
        ->arg('$messageSerializer', service(BridgeConstantsInterface::SERVICE_BATCH_MESSAGE_SERIALIZER))
        ->arg('$class', '%' . BridgeConstantsInterface::PARAM_BATCH_ITEM_CLASS . '%');
};
