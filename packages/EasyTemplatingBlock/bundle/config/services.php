<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyTemplatingBlock\Bundle\Enum\ConfigParam;
use EonX\EasyTemplatingBlock\Bundle\Enum\ConfigServiceId;
use EonX\EasyTemplatingBlock\Bundle\Enum\ConfigTag;
use EonX\EasyTemplatingBlock\Common\Renderer\TemplatingEventRenderer;
use EonX\EasyTemplatingBlock\Common\Renderer\TemplatingEventRendererInterface;
use EonX\EasyTemplatingBlock\Common\Renderer\TextBlockRenderer;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services->set(ConfigServiceId::TextBlockRenderer->value, TextBlockRenderer::class);

    $services
        ->set(TemplatingEventRendererInterface::class, TemplatingEventRenderer::class)
        ->arg('$providers', tagged_iterator(ConfigTag::TemplatingBlockProvider->value))
        ->arg('$renderers', tagged_iterator(ConfigTag::TemplatingBlockRenderer->value))
        ->arg('$isDebug', param(ConfigParam::IsDebug->value));
};
