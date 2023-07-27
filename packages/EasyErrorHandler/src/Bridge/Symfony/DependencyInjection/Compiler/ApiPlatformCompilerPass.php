<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ApiPlatformCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->getParameter(BridgeConstantsInterface::PARAM_OVERRIDE_API_PLATFORM_LISTENER) === false) {
            return;
        }

        $container->removeDefinition('api_platform.listener.exception.validation');
    }
}
