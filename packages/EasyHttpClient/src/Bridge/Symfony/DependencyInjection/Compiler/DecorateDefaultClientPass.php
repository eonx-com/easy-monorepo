<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyHttpClient\Bridge\BridgeConstantsInterface;
use EonX\EasyHttpClient\Implementations\Symfony\WithEventsHttpClient;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class DecorateDefaultClientPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private const DECORATION_SERVICE_ID = 'easy_http_client.decorate_default';

    /**
     * @var string
     */
    private const DEFAULT_CLIENT_ID = 'http_client';

    public function process(ContainerBuilder $container): void
    {
        // Apply only if enabled and container has default client definition
        if ($this->isEnabled($container) === false || $container->has(self::DEFAULT_CLIENT_ID) === false) {
            return;
        }

        $def = (new Definition(WithEventsHttpClient::class))
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setDecoratedService(self::DEFAULT_CLIENT_ID);

        $container->setDefinition(self::DECORATION_SERVICE_ID, $def);
    }

    private function isEnabled(ContainerBuilder $container): bool
    {
        if ($container->hasParameter(BridgeConstantsInterface::PARAM_DECORATE_DEFAULT_CLIENT) === false) {
            return false;
        }

        return (bool)$container->getParameter(BridgeConstantsInterface::PARAM_DECORATE_DEFAULT_CLIENT);
    }
}
