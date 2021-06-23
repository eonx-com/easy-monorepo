<?php

declare(strict_types=1);

use EonX\EasyBatch\Interfaces\BatchManagerInterface;
use EonX\EasyBatch\BatchManager;
use EonX\EasyBatch\Interfaces\AsyncDispatcherInterface;
use EonX\EasyBatch\Bridge\Symfony\Messenger\AsyncDispatcher;
use EonX\EasyBatch\Interfaces\BatchFactoryInterface;
use EonX\EasyBatch\Factories\BatchFactory;
use EonX\EasyBatch\Interfaces\BatchItemFactoryInterface;
use EonX\EasyBatch\Factories\BatchItemFactory;
use EonX\EasyBatch\Interfaces\BatchObjectIdStrategyInterface;
use EonX\EasyBatch\IdStrategies\UuidV4Strategy;
use EonX\EasyBatch\Bridge\BridgeConstantsInterface;
use EonX\EasyBatch\Bridge\Symfony\Messenger\DispatchBatchMiddleware;
use EonX\EasyBatch\Bridge\Symfony\Messenger\ProcessBatchItemMiddleware;
use EonX\EasyBatch\Interfaces\BatchRepositoryInterface;
use EonX\EasyBatch\Repositories\BatchRepository;
use EonX\EasyBatch\Interfaces\BatchItemRepositoryInterface;
use EonX\EasyBatch\Repositories\BatchItemRepository;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind('$dateTimeFormat', '%' . BridgeConstantsInterface::PARAM_DATE_TIME_FORMAT . '%')
        ->bind('$eventDispatcher', ref(EventDispatcherInterface::class));

    // AsyncDispatcher
    $services->set(AsyncDispatcherInterface::class, AsyncDispatcher::class);

    // Factories
    $services
        ->set(BatchFactoryInterface::class, BatchFactory::class)
        ->arg('$class', '%' . BridgeConstantsInterface::PARAM_BATCH_CLASS . '%');

    $services
        ->set(BatchItemFactoryInterface::class, BatchItemFactory::class)
        ->arg('$class', '%' . BridgeConstantsInterface::PARAM_BATCH_ITEM_CLASS . '%');

    // IdStrategies
    $services
        ->set(BatchObjectIdStrategyInterface::class, UuidV4Strategy::class)
        ->alias(BridgeConstantsInterface::SERVICE_BATCH_ID_STRATEGY, BatchObjectIdStrategyInterface::class)
        ->alias(BridgeConstantsInterface::SERVICE_BATCH_ITEM_ID_STRATEGY, BatchObjectIdStrategyInterface::class);

    // Listeners
    foreach (BridgeConstantsInterface::LISTENERS as $listener) {
        $services
            ->set($listener)
            ->tag($listener);
    }

    // Manager
    $services->set(BatchManagerInterface::class, BatchManager::class);

    // Middleware
    $services->set(DispatchBatchMiddleware::class);
    $services->set(ProcessBatchItemMiddleware::class);

    // Repositories
    $services
        ->set(BatchRepositoryInterface::class, BatchRepository::class)
        ->arg('$factory', ref(BatchFactoryInterface::class))
        ->arg('$idStrategy', ref(BridgeConstantsInterface::SERVICE_BATCH_ID_STRATEGY))
        ->arg('$table', '%' . BridgeConstantsInterface::PARAM_BATCH_TABLE . '%');

    $services
        ->set(BatchItemRepositoryInterface::class, BatchItemRepository::class)
        ->arg('$factory', ref(BatchItemFactoryInterface::class))
        ->arg('$idStrategy', ref(BridgeConstantsInterface::SERVICE_BATCH_ITEM_ID_STRATEGY))
        ->arg('$table', '%' . BridgeConstantsInterface::PARAM_BATCH_ITEM_TABLE . '%');
};
