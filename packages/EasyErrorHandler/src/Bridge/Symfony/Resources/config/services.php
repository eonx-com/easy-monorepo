<?php

declare(strict_types=1);

use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface;
use EonX\EasyErrorHandler\Bridge\Symfony\Listener\ExceptionEventListener;
use EonX\EasyErrorHandler\Bridge\Symfony\Translator;
use EonX\EasyErrorHandler\ErrorDetailsResolver;
use EonX\EasyErrorHandler\ErrorHandler;
use EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorResponseFactoryInterface;
use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;
use EonX\EasyErrorHandler\Response\ErrorResponseFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // ErrorDetailsResolver
    $services->set(ErrorDetailsResolverInterface::class, ErrorDetailsResolver::class);

    // ErrorHandler
    $services
        ->set(ErrorHandlerInterface::class, ErrorHandler::class)
        ->arg('$builderProviders', tagged_iterator(BridgeConstantsInterface::TAG_ERROR_RESPONSE_BUILDER_PROVIDER))
        ->arg('$reporterProviders', tagged_iterator(BridgeConstantsInterface::TAG_ERROR_REPORTER_PROVIDER))
        ->arg('$isVerbose', '%' . BridgeConstantsInterface::PARAM_IS_VERBOSE . '%');

    // EventListener
    $services
        ->set(ExceptionEventListener::class)
        ->tag('kernel.event_listener');

    // ResponseFactory
    $services->set(ErrorResponseFactoryInterface::class, ErrorResponseFactory::class);

    // Translator
    $services
        ->set(TranslatorInterface::class, Translator::class)
        ->arg('$domain', '%' . BridgeConstantsInterface::PARAM_TRANSLATION_DOMAIN . '%');
};
