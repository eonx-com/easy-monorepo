<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyTemplatingBlock\Bridge\BridgeConstantsInterface;
use EonX\EasyTemplatingBlock\Interfaces\TemplatingEventRendererInterface;
use EonX\EasyTemplatingBlock\Renderers\TextBlockRenderer;
use EonX\EasyTemplatingBlock\TemplatingEventRenderer;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services->set(BridgeConstantsInterface::SERVICE_TEXT_BLOCK_RENDERER, TextBlockRenderer::class);

    $services
        ->set(TemplatingEventRendererInterface::class, TemplatingEventRenderer::class)
        ->arg('$providers', tagged_iterator(BridgeConstantsInterface::TAG_TEMPLATING_BLOCK_PROVIDER))
        ->arg('$renderers', tagged_iterator(BridgeConstantsInterface::TAG_TEMPLATING_BLOCK_RENDERER))
        ->arg('$isDebug', '%' . BridgeConstantsInterface::PARAM_IS_DEBUG . '%');
};
