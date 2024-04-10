<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyErrorHandler\Bridge\Symfony\Commands\AnalyzeErrorCodesCommand;
use EonX\EasyErrorHandler\Bridge\Symfony\DataCollector\ErrorHandlerDataCollector;
use EonX\EasyErrorHandler\Bridge\Symfony\Listener\ConsoleErrorEventListener;
use EonX\EasyErrorHandler\Bridge\Symfony\Listener\ExceptionEventListener;
use EonX\EasyErrorHandler\Bridge\Symfony\Messenger\ReportErrorEventListener;
use EonX\EasyErrorHandler\Bridge\Symfony\Translator;
use EonX\EasyErrorHandler\BridgeConstantsInterface;
use EonX\EasyErrorHandler\ErrorDetailsResolver;
use EonX\EasyErrorHandler\ErrorHandler;
use EonX\EasyErrorHandler\ErrorLogLevelResolver;
use EonX\EasyErrorHandler\Interfaces\ErrorCodesGroupProcessorInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorLogLevelResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorResponseFactoryInterface;
use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;
use EonX\EasyErrorHandler\Interfaces\VerboseStrategyInterface;
use EonX\EasyErrorHandler\Processors\ErrorCodesGroupProcessor;
use EonX\EasyErrorHandler\Providers\ErrorCodesFromEnumProvider;
use EonX\EasyErrorHandler\Providers\ErrorCodesFromInterfaceProvider;
use EonX\EasyErrorHandler\Response\ErrorResponseFactory;
use EonX\EasyErrorHandler\Verbose\ChainVerboseStrategy;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // ErrorDetailsResolver
    $services
        ->set(ErrorDetailsResolverInterface::class, ErrorDetailsResolver::class)
        ->arg(
            '$translateInternalMessages',
            param(BridgeConstantsInterface::PARAM_TRANSLATE_INTERNAL_ERROR_MESSAGES_ENABLED)
        )
        ->arg(
            '$internalMessagesLocale',
            param(BridgeConstantsInterface::PARAM_TRANSLATE_INTERNAL_ERROR_MESSAGES_LOCALE)
        );

    // ErrorLogLevelResolver
    $services
        ->set(ErrorLogLevelResolverInterface::class, ErrorLogLevelResolver::class)
        ->arg('$exceptionLogLevels', param(BridgeConstantsInterface::PARAM_LOGGER_EXCEPTION_LOG_LEVELS));

    // ErrorHandler
    $services
        ->set(ErrorHandlerInterface::class, ErrorHandler::class)
        ->arg('$builderProviders', tagged_iterator(BridgeConstantsInterface::TAG_ERROR_RESPONSE_BUILDER_PROVIDER))
        ->arg('$reporterProviders', tagged_iterator(BridgeConstantsInterface::TAG_ERROR_REPORTER_PROVIDER))
        ->arg('$ignoredExceptionsForReport', param(BridgeConstantsInterface::PARAM_IGNORED_EXCEPTIONS))
        ->arg(
            '$reportRetryableExceptionAttempts',
            param(BridgeConstantsInterface::PARAM_REPORT_RETRYABLE_EXCEPTION_ATTEMPTS)
        )
        ->arg('$skipReportedExceptions', param(BridgeConstantsInterface::PARAM_SKIP_REPORTED_EXCEPTIONS));

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
        ->arg('$domain', param(BridgeConstantsInterface::PARAM_TRANSLATION_DOMAIN));

    // Verbose
    $services
        ->set(VerboseStrategyInterface::class, ChainVerboseStrategy::class)
        ->arg('$drivers', tagged_iterator(BridgeConstantsInterface::TAG_VERBOSE_STRATEGY_DRIVER))
        ->arg('$defaultIsVerbose', param(BridgeConstantsInterface::PARAM_IS_VERBOSE));

    // Error codes providers
    $services->set('error_codes_provider.from_interface', ErrorCodesFromInterfaceProvider::class)
        ->arg('$errorCodesInterface', param(BridgeConstantsInterface::PARAM_ERROR_CODES_INTERFACE));

    $services->set('error_codes_provider.from_enum', ErrorCodesFromEnumProvider::class)
        ->arg('$projectDir', param('kernel.project_dir') . '/src');

    // Error codes group processor
    $services->set(ErrorCodesGroupProcessorInterface::class, ErrorCodesGroupProcessor::class)
        ->arg('$categorySize', param(BridgeConstantsInterface::PARAM_ERROR_CODES_CATEGORY_SIZE))
        ->arg('$errorCodesProviders', [
            service('error_codes_provider.from_interface'),
            service('error_codes_provider.from_enum'),
        ]);

    // Console command
    $services
        ->set(AnalyzeErrorCodesCommand::class)
        ->tag('console.command');
};
