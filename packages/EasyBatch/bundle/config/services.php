<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyBatch\Bundle\Enum\ConfigParam;
use EonX\EasyBatch\Bundle\Enum\ConfigServiceId;
use EonX\EasyBatch\Common\Dispatcher\AsyncDispatcherInterface;
use EonX\EasyBatch\Common\Dispatcher\BatchItemDispatcher;
use EonX\EasyBatch\Common\Factory\BatchFactory;
use EonX\EasyBatch\Common\Factory\BatchFactoryInterface;
use EonX\EasyBatch\Common\Factory\BatchItemFactory;
use EonX\EasyBatch\Common\Factory\BatchItemFactoryInterface;
use EonX\EasyBatch\Common\Iterator\BatchItemIteratorInterface;
use EonX\EasyBatch\Common\Manager\BatchObjectManager;
use EonX\EasyBatch\Common\Manager\BatchObjectManagerInterface;
use EonX\EasyBatch\Common\Persister\BatchItemPersister;
use EonX\EasyBatch\Common\Persister\BatchPersister;
use EonX\EasyBatch\Common\Processor\BatchItemProcessor;
use EonX\EasyBatch\Common\Processor\BatchProcessor;
use EonX\EasyBatch\Common\Repository\BatchItemRepositoryInterface;
use EonX\EasyBatch\Common\Repository\BatchRepositoryInterface;
use EonX\EasyBatch\Common\Serializer\MessageSerializer;
use EonX\EasyBatch\Common\Serializer\MessageSerializerInterface;
use EonX\EasyBatch\Common\Strategy\BatchObjectIdStrategyInterface;
use EonX\EasyBatch\Common\Strategy\UuidStrategy;
use EonX\EasyBatch\Common\Transformer\BatchItemTransformer;
use EonX\EasyBatch\Common\Transformer\BatchTransformer;
use EonX\EasyBatch\Doctrine\Iterator\BatchItemIterator;
use EonX\EasyBatch\Doctrine\Repository\BatchItemRepository;
use EonX\EasyBatch\Doctrine\Repository\BatchRepository;
use EonX\EasyBatch\Messenger\Dispatcher\AsyncDispatcher;
use EonX\EasyBatch\Messenger\ExceptionHandler\BatchItemExceptionHandler;
use EonX\EasyBatch\Messenger\Factory\BatchItemLockFactory;
use EonX\EasyBatch\Messenger\Factory\BatchItemLockFactoryInterface;
use EonX\EasyBatch\Messenger\MessageHandler\ProcessBatchForBatchItemMessageHandler;
use EonX\EasyBatch\Messenger\MessageHandler\UpdateBatchItemMessageHandler;
use EonX\EasyBatch\Messenger\Middleware\DispatchBatchMiddleware;
use EonX\EasyBatch\Messenger\Middleware\ProcessBatchItemMiddleware;
use EonX\EasyBatch\Messenger\Serializer\HandlerFailedExceptionMessageSerializer;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // AsyncDispatcher
    $services->set(AsyncDispatcherInterface::class, AsyncDispatcher::class);

    // Factories
    $services
        ->set(BatchFactoryInterface::class, BatchFactory::class)
        ->arg('$transformer', service(ConfigServiceId::BatchTransformer->value));

    $services
        ->set(BatchItemFactoryInterface::class, BatchItemFactory::class)
        ->arg('$transformer', service(ConfigServiceId::BatchItemTransformer->value));

    // IdStrategies
    $services
        ->set(BatchObjectIdStrategyInterface::class, UuidStrategy::class)
        ->alias(ConfigServiceId::BatchIdStrategy->value, BatchObjectIdStrategyInterface::class)
        ->alias(ConfigServiceId::BatchItemIdStrategy->value, BatchObjectIdStrategyInterface::class);

    // Internals
    $services
        ->set(BatchItemDispatcher::class)
        ->set(BatchItemPersister::class)
        ->set(BatchPersister::class)
        ->set(BatchItemProcessor::class)
        ->set(BatchProcessor::class);
    $services
        ->set(BatchItemIteratorInterface::class, BatchItemIterator::class)
        ->arg('$batchItemsPerPage', param(ConfigParam::BatchItemPerPage->value));

    // Manager
    $services
        ->set(BatchObjectManagerInterface::class, BatchObjectManager::class);

    // Messenger
    $services
        ->set(BatchItemExceptionHandler::class)
        ->arg('$batchItemTransformer', service(ConfigServiceId::BatchItemTransformer->value))
        ->arg('$container', service('service_container'));

    $services
        ->set(BatchItemLockFactoryInterface::class, BatchItemLockFactory::class)
        ->arg('$ttl', param(ConfigParam::LockTtl->value));

    $services
        ->set(DispatchBatchMiddleware::class)
        ->set(ProcessBatchItemMiddleware::class);
    $services
        ->set(ProcessBatchForBatchItemMessageHandler::class)
        ->arg('$dateTimeFormat', param(ConfigParam::DateTimeFormat->value));
    $services
        ->set(UpdateBatchItemMessageHandler::class)
        ->arg('$dateTimeFormat', param(ConfigParam::DateTimeFormat->value));

    // Repositories
    $services
        ->set(BatchRepositoryInterface::class, BatchRepository::class)
        ->arg('$factory', service(BatchFactoryInterface::class))
        ->arg('$idStrategy', service(ConfigServiceId::BatchIdStrategy->value))
        ->arg('$table', param(ConfigParam::BatchTable->value))
        ->arg('$transformer', service(ConfigServiceId::BatchTransformer->value));

    $services
        ->set(BatchItemRepositoryInterface::class, BatchItemRepository::class)
        ->arg('$factory', service(BatchItemFactoryInterface::class))
        ->arg('$idStrategy', service(ConfigServiceId::BatchItemIdStrategy->value))
        ->arg('$table', param(ConfigParam::BatchItemTable->value))
        ->arg('$transformer', service(ConfigServiceId::BatchItemTransformer->value));

    // Serializer
    $services->set(MessageSerializerInterface::class, MessageSerializer::class);

    $services->alias(ConfigServiceId::BatchMessageSerializer->value, MessageSerializerInterface::class);

    $services->set(HandlerFailedExceptionMessageSerializer::class)
        ->decorate(ConfigServiceId::BatchMessageSerializer->value)
        ->args([service('.inner')]);

    // Transformers
    $services
        ->set(ConfigServiceId::BatchTransformer->value, BatchTransformer::class)
        ->arg('$class', param(ConfigParam::BatchClass->value))
        ->arg('$dateTimeFormat', param(ConfigParam::DateTimeFormat->value));

    $services
        ->set(ConfigServiceId::BatchItemTransformer->value, BatchItemTransformer::class)
        ->arg('$messageSerializer', service(ConfigServiceId::BatchMessageSerializer->value))
        ->arg('$class', param(ConfigParam::BatchItemClass->value))
        ->arg('$dateTimeFormat', param(ConfigParam::DateTimeFormat->value));
};
