<?php

declare(strict_types=1);

use EonX\EasyBatch\Interfaces\BatchObjectApproverInterface;
use EonX\EasyBatch\BatchObjectApprover;
use EonX\EasyBatch\Interfaces\BatchCancellerInterface;
use EonX\EasyBatch\BatchCanceller;
use EonX\EasyBatch\Interfaces\BatchDispatcherInterface;
use EonX\EasyBatch\Bridge\Symfony\Messenger\BatchDispatcher;
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
use EonX\EasyBatch\Interfaces\BatchUpdaterInterface;
use EonX\EasyBatch\BatchUpdater;
use EonX\EasyBatch\Interfaces\BatchStoreInterface;
use EonX\EasyBatch\Stores\DoctrineDbalStore;
use EonX\EasyBatch\Interfaces\BatchItemStoreInterface;
use EonX\EasyBatch\Interfaces\BatchItemProcessorInterface;
use EonX\EasyBatch\BatchItemProcessor;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use EonX\EasyBatch\Interfaces\BatchItemUpdaterInterface;
use EonX\EasyBatch\BatchItemUpdater;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind('$dateTimeFormat', '%' . BridgeConstantsInterface::PARAM_DATE_TIME_FORMAT . '%')
        ->bind('$dispatcher', ref(EventDispatcherInterface::class));

    // Approver
    $services->set(BatchObjectApproverInterface::class, BatchObjectApprover::class);

    // Canceller
    $services->set(BatchCancellerInterface::class, BatchCanceller::class);

    // Dispatcher
    $services->set(BatchDispatcherInterface::class, BatchDispatcher::class);

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

    // Middleware
    $services->set(DispatchBatchMiddleware::class);
    $services->set(ProcessBatchItemMiddleware::class);

    // Processor
    $services->set(BatchItemProcessorInterface::class, BatchItemProcessor::class);

    // Repositories
    $services->set(BatchRepositoryInterface::class, BatchRepository::class);
    $services->set(BatchItemRepositoryInterface::class, BatchItemRepository::class);

    // Stores
    $services
        ->set(BatchStoreInterface::class, DoctrineDbalStore::class)
        ->arg('$table', '%' . BridgeConstantsInterface::PARAM_BATCH_TABLE . '%');

    $services
        ->set(BatchItemStoreInterface::class, DoctrineDbalStore::class)
        ->arg('$table', '%' . BridgeConstantsInterface::PARAM_BATCH_ITEM_TABLE . '%');

    // Updaters
    $services->set(BatchUpdaterInterface::class, BatchUpdater::class);
    $services->set(BatchItemUpdaterInterface::class, BatchItemUpdater::class);
};
