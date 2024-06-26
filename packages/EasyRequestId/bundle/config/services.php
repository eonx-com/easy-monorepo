<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyRequestId\Bundle\Enum\ConfigParam;
use EonX\EasyRequestId\Common\Listener\RequestListener;
use EonX\EasyRequestId\Common\Provider\RequestIdProvider;
use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;
use EonX\EasyRequestId\Common\Resolver\FallbackResolverInterface;
use EonX\EasyRequestId\Common\Resolver\UuidFallbackResolver;
use EonX\EasyRequestId\Messenger\Listener\SendMessageToTransportsListener;
use EonX\EasyRequestId\Messenger\Listener\WorkerMessageReceivedListener;
use EonX\EasyRequestId\Twig\Extension\RequestIdTwigExtension;
use Twig\Extension\AbstractExtension;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Fallback
    $services->set(FallbackResolverInterface::class, UuidFallbackResolver::class);

    // RequestIdProvider
    $services
        ->set(RequestIdProviderInterface::class, RequestIdProvider::class)
        ->arg('$correlationIdHeaderName', param(ConfigParam::HttpHeaderCorrelationId->value))
        ->arg('$requestIdHeaderName', param(ConfigParam::HttpHeaderRequestId->value));

    // Listener
    $services
        ->set(RequestListener::class)
        ->tag('kernel.event_listener', [
            'priority' => 10000,
        ]);

    // Messenger
    $services
        ->set(SendMessageToTransportsListener::class)
        ->tag('kernel.event_listener');

    $services
        ->set(WorkerMessageReceivedListener::class)
        ->tag('kernel.event_listener');

    // Twig
    if (\class_exists(AbstractExtension::class)) {
        $services->set(RequestIdTwigExtension::class);
    }
};
