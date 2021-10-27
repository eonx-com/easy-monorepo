<?php

declare(strict_types=1);

use EonX\EasyTemplatingBlock\Bridge\BridgeConstantsInterface;
use EonX\EasyTemplatingBlock\Bridge\Symfony\Twig\TwigBlockExtension;
use EonX\EasyTemplatingBlock\Bridge\Symfony\Twig\TwigBlockRenderer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services->set(TwigBlockExtension::class);

    $services->set(BridgeConstantsInterface::SERVICE_TWIG_BLOCK_RENDERER, TwigBlockRenderer::class);
};
