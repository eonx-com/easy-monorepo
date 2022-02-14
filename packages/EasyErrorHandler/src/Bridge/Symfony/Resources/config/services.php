<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface;
use EonX\EasyErrorHandler\Bridge\Symfony\DataCollector\ErrorHandlerDataCollector;
use EonX\EasyErrorHandler\Bridge\Symfony\Listener\ConsoleErrorEventListener;
use EonX\EasyErrorHandler\Bridge\Symfony\Listener\ExceptionEventListener;
use EonX\EasyErrorHandler\Bridge\Symfony\Messenger\ReportErrorEventListener;
use EonX\EasyErrorHandler\Bridge\Symfony\Translator;
use EonX\EasyErrorHandler\ErrorDetailsResolver;
use EonX\EasyErrorHandler\ErrorHandler;
use EonX\EasyErrorHandler\ErrorLogLevelResolver;
use EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorLogLevelResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorResponseFactoryInterface;
use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;
use EonX\EasyErrorHandler\Response\ErrorResponseFactory;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // ErrorDetailsResolver
    $services->set(ErrorDetailsResolverInterface::class, ErrorDetailsResolver::class);

    // ErrorLogLevelResolver
    $services
        ->set(ErrorLogLevelResolverInterface::class, ErrorLogLevelResolver::class)
        ->arg('$exceptionLogLevels', '%' . BridgeConstantsInterface::PARAM_LOGGER_EXCEPTION_LOG_LEVELS . '%');

    // ErrorHandler
    $services
        ->set(ErrorHandlerInterface::class, ErrorHandler::class)
        ->arg('$builderProviders', tagged_iterator(BridgeConstantsInterface::TAG_ERROR_RESPONSE_BUILDER_PROVIDER))
        ->arg('$reporterProviders', tagged_iterator(BridgeConstantsInterface::TAG_ERROR_REPORTER_PROVIDER))
        ->arg('$isVerbose', '%' . BridgeConstantsInterface::PARAM_IS_VERBOSE . '%')
        ->arg('$ignoredExceptionsForReport', '%' . BridgeConstantsInterface::PARAM_IGNORED_EXCEPTIONS . '%');

    $services->set(ErrorHandlerDataCollector::class)
        ->tag('data_collector', [
            'id' => 'error_handler.error_handler_collector',
            'template' => '@EasyErrorHandlerSymfony/Collector/error_handler_collector.html.twig',
        ]);

    // Console EventListener
    $services
        ->set(ConsoleErrorEventListener::class)
        ->tag('kernel.event_listener');

    // EventListener
    $services
        ->set(ExceptionEventListener::class)
        ->tag('kernel.event_listener');

    // Messenger EventListener
    $services
        ->set(ReportErrorEventListener::class)
        ->tag('kernel.event_listener');

    // ResponseFactory
    $services->set(ErrorResponseFactoryInterface::class, ErrorResponseFactory::class);

    // Translator
    $services
        ->set(TranslatorInterface::class, Translator::class)
        ->arg('$domain', '%' . BridgeConstantsInterface::PARAM_TRANSLATION_DOMAIN . '%');
};
