<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyTemplatingBlock\Bundle\Enum\ConfigServiceId;
use EonX\EasyTemplatingBlock\Twig\Extension\TwigBlockExtension;
use EonX\EasyTemplatingBlock\Twig\Renderer\TwigBlockRenderer;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services->set(TwigBlockExtension::class);

    $services->set(ConfigServiceId::TwigBlockRenderer->value, TwigBlockRenderer::class);
};
