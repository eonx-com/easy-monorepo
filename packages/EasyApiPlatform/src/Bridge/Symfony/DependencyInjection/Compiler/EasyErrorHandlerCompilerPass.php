<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyErrorHandler\Bridge\Symfony\EasyErrorHandlerSymfonyBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EasyErrorHandlerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (ContainerBuilder::willBeAvailable(
                'eonx-com/easy-error-handler',
                EasyErrorHandlerSymfonyBundle::class,
                []
            ) === false) {
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
