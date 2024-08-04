<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyErrorHandler\Bundle\Enum\ConfigParam;
use EonX\EasyErrorHandler\Bundle\Enum\ConfigTag;
use EonX\EasyErrorHandler\Common\DataCollector\ErrorHandlerDataCollector;
use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandler;
use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Common\Factory\ErrorResponseFactory;
use EonX\EasyErrorHandler\Common\Factory\ErrorResponseFactoryInterface;
use EonX\EasyErrorHandler\Common\Listener\ConsoleErrorListener;
use EonX\EasyErrorHandler\Common\Listener\ExceptionListener;
use EonX\EasyErrorHandler\Common\Resolver\ErrorDetailsResolver;
use EonX\EasyErrorHandler\Common\Resolver\ErrorDetailsResolverInterface;
use EonX\EasyErrorHandler\Common\Resolver\ErrorLogLevelResolver;
use EonX\EasyErrorHandler\Common\Resolver\ErrorLogLevelResolverInterface;
use EonX\EasyErrorHandler\Common\Strategy\ChainVerboseStrategy;
use EonX\EasyErrorHandler\Common\Strategy\VerboseStrategyInterface;
use EonX\EasyErrorHandler\Common\Translator\Translator;
use EonX\EasyErrorHandler\Common\Translator\TranslatorInterface;
use EonX\EasyErrorHandler\ErrorCodes\Command\AnalyzeErrorCodesCommand;
use EonX\EasyErrorHandler\ErrorCodes\Processor\ErrorCodesGroupProcessor;
use EonX\EasyErrorHandler\ErrorCodes\Processor\ErrorCodesGroupProcessorInterface;
use EonX\EasyErrorHandler\ErrorCodes\Provider\ErrorCodesFromEnumProvider;
use EonX\EasyErrorHandler\ErrorCodes\Provider\ErrorCodesFromInterfaceProvider;
use EonX\EasyErrorHandler\Messenger\Listener\WorkerMessageFailedListener;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // ErrorDetailsResolver
    $services
        ->set(ErrorDetailsResolverInterface::class, ErrorDetailsResolver::class)
        ->arg('$translateInternalMessages', param(ConfigParam::TranslateInternalErrorMessagesEnabled->value))
        ->arg('$internalMessagesLocale', param(ConfigParam::TranslateInternalErrorMessagesLocale->value));

    // ErrorLogLevelResolver
    $services
        ->set(ErrorLogLevelResolverInterface::class, ErrorLogLevelResolver::class)
        ->arg('$exceptionLogLevels', param(ConfigParam::LoggerExceptionLogLevels->value));

    // ErrorHandler
    $services
        ->set(ErrorHandlerInterface::class, ErrorHandler::class)
        ->arg('$builderProviders', tagged_iterator(ConfigTag::ErrorResponseBuilderProvider->value))
        ->arg('$reporterProviders', tagged_iterator(ConfigTag::ErrorReporterProvider->value))
        ->arg('$ignoredExceptionsForReport', param(ConfigParam::IgnoredExceptions->value))
        ->arg(
            '$reportRetryableExceptionAttempts',
            param(ConfigParam::ReportRetryableExceptionAttempts->value)
        )
        ->arg('$skipReportedExceptions', param(ConfigParam::SkipReportedExceptions->value));

    $services->set(ErrorHandlerDataCollector::class);

    // Console EventListener
    $services
        ->set(ConsoleErrorListener::class)
        ->tag('kernel.event_listener');

    // EventListener
    $services
        ->set(ExceptionListener::class)
        ->tag('kernel.event_listener');

    // Messenger EventListener
    $services
        ->set(WorkerMessageFailedListener::class)
        ->tag('kernel.event_listener');

    // ResponseFactory
    $services->set(ErrorResponseFactoryInterface::class, ErrorResponseFactory::class);

    // Translator
    $services
        ->set(TranslatorInterface::class, Translator::class)
        ->arg('$domain', param(ConfigParam::TranslationDomain->value));

    // Verbose
    $services
        ->set(VerboseStrategyInterface::class, ChainVerboseStrategy::class)
        ->arg('$drivers', tagged_iterator(ConfigTag::VerboseStrategyDriver->value))
        ->arg('$defaultIsVerbose', param(ConfigParam::IsVerbose->value));

    // Error codes providers
    $services->set('error_codes_provider.from_interface', ErrorCodesFromInterfaceProvider::class)
        ->arg('$errorCodesInterface', param(ConfigParam::ErrorCodesInterface->value));

    $services->set('error_codes_provider.from_enum', ErrorCodesFromEnumProvider::class)
        ->arg('$projectDir', param('kernel.project_dir') . '/src');

    // Error codes group processor
    $services->set(ErrorCodesGroupProcessorInterface::class, ErrorCodesGroupProcessor::class)
        ->arg('$categorySize', param(ConfigParam::ErrorCodesCategorySize->value))
        ->arg('$errorCodesProviders', [
            service('error_codes_provider.from_interface'),
            service('error_codes_provider.from_enum'),
        ]);

    // Console command
    $services
        ->set(AnalyzeErrorCodesCommand::class)
        ->tag('console.command');
};
