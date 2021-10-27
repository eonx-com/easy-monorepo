<?php

declare(strict_types=1);

use EonX\EasyTemplatingBlock\Bridge\BridgeConstantsInterface;
use EonX\EasyTemplatingBlock\Interfaces\TemplatingEventRendererInterface;
use EonX\EasyTemplatingBlock\TemplatingEventRenderer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(TemplatingEventRendererInterface::class, TemplatingEventRenderer::class)
        ->arg('$providers', tagged_iterator(BridgeConstantsInterface::TAG_TEMPLATING_BLOCK_PROVIDER))
        ->arg('$renderers', tagged_iterator(BridgeConstantsInterface::TAG_TEMPLATING_BLOCK_RENDERER))
        ->arg('$isDebug', '%' . BridgeConstantsInterface::PARAM_IS_DEBUG . '%');
};
