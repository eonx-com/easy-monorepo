<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\DependencyInjection\Compiler;

use EonX\EasyErrorHandler\BridgeConstantsInterface;
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

        // We need this to handle \TypeError in \EonX\EasyErrorHandler\Bridge\Symfony\Builder\ApiPlatformValidationErrorResponseBuilder
        // @see \Symfony\Component\HttpKernel\HttpKernel
        // @see https://symfony.com/doc/current/reference/configuration/framework.html#handle-all-throwables
        $container->getDefinition('http_kernel')
            ->setArgument('$handleAllThrowables', true);
    }
}
