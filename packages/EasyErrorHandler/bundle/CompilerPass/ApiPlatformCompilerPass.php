<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bundle\CompilerPass;

use EonX\EasyErrorHandler\Bundle\Enum\ConfigParam;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ApiPlatformCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->getParameter(ConfigParam::OverrideApiPlatformListener->value) === false) {
            return;
        }

        $container->removeDefinition('api_platform.listener.exception.validation');

        // We need this to handle \TypeError in \EonX\EasyErrorHandler\ApiPlatform\Builder\ApiPlatformValidationErrorResponseBuilder
        // @see \Symfony\Component\HttpKernel\HttpKernel
        // @see https://symfony.com/doc/current/reference/configuration/framework.html#handle-all-throwables
        $container->getDefinition('http_kernel')
            ->setArgument('$handleAllThrowables', true);
    }
}