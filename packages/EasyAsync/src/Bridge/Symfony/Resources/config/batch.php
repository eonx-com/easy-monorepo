<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyAsync\Batch\BatchCanceller;
use EonX\EasyAsync\Batch\BatchFactory;
use EonX\EasyAsync\Batch\BatchItemFactory;
use EonX\EasyAsync\Batch\BatchItemProcessor;
use EonX\EasyAsync\Batch\BatchUpdater;
use EonX\EasyAsync\Batch\Store\DoctrineDbalBatchItemStore;
use EonX\EasyAsync\Batch\Store\DoctrineDbalBatchStore;
use EonX\EasyAsync\Bridge\BridgeConstantsInterface;
use EonX\EasyAsync\Bridge\Doctrine\DbalStatementsProvider;
use EonX\EasyAsync\Bridge\Symfony\Messenger\BatchDispatcher;
use EonX\EasyAsync\Bridge\Symfony\Messenger\DispatchBatchMiddleware;
use EonX\EasyAsync\Bridge\Symfony\Messenger\ProcessBatchItemMiddleware;
use EonX\EasyAsync\Interfaces\Batch\BatchCancellerInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchDispatcherInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchFactoryInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchItemFactoryInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchItemProcessorInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchItemStoreInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchStoreInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchUpdaterInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Canceller
    $services->set(BatchCancellerInterface::class, BatchCanceller::class);

    // Dispatcher
    $services->set(BatchDispatcherInterface::class, BatchDispatcher::class);

    // Factories
    $services
        ->set(BatchFactoryInterface::class, BatchFactory::class)
        ->arg('$class', '%' . BridgeConstantsInterface::PARAM_BATCH_DEFAULT_CLASS . '%');

    $services->set(BatchItemFactoryInterface::class, BatchItemFactory::class);

    // Messenger
    $services
        ->set(DispatchBatchMiddleware::class)
        ->set(ProcessBatchItemMiddleware::class);

    // Processor
    $services->set(BatchItemProcessorInterface::class, BatchItemProcessor::class);

    // Stores (Default doctrine dbal)
    $services
        ->set(BatchStoreInterface::class, DoctrineDbalBatchStore::class)
        ->arg('$conn', service('doctrine.dbal.default_connection'))
        ->arg('$table', '%' . BridgeConstantsInterface::PARAM_BATCHES_TABLE . '%');

    $services
        ->set(BatchItemStoreInterface::class, DoctrineDbalBatchItemStore::class)
        ->arg('$conn', service('doctrine.dbal.default_connection'))
        ->arg('$table', '%' . BridgeConstantsInterface::PARAM_BATCH_ITEMS_TABLE . '%');

    // Doctrine Statements Provider (Helper)
    $services
        ->set(DbalStatementsProvider::class)
        ->arg('$conn', service('doctrine.dbal.default_connection'))
        ->arg('$batchesTable', '%' . BridgeConstantsInterface::PARAM_BATCHES_TABLE . '%')
        ->arg('$batchItemsTable', '%' . BridgeConstantsInterface::PARAM_BATCH_ITEMS_TABLE . '%')
        ->public();

    // Updater
    $services->set(BatchUpdaterInterface::class, BatchUpdater::class);
};
