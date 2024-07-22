<?php
declare(strict_types=1);

<<<<<<<< HEAD:packages/EasyApiPlatform/bundle/CompilerPass/EasyErrorHandlerCompilerPass.php
namespace EonX\EasyApiPlatform\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyErrorHandler\Bridge\Symfony\EasyErrorHandlerSymfonyBundle;
========
namespace EonX\EasyErrorHandler\Bundle\CompilerPass;

use EonX\EasyErrorHandler\Bundle\Enum\ConfigParam;
>>>>>>>> refs/heads/6.x:packages/EasyErrorHandler/bundle/CompilerPass/ApiPlatformCompilerPass.php
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EasyErrorHandlerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
<<<<<<<< HEAD:packages/EasyApiPlatform/bundle/CompilerPass/EasyErrorHandlerCompilerPass.php
        if (ContainerBuilder::willBeAvailable(
                'eonx-com/easy-error-handler',
                EasyErrorHandlerSymfonyBundle::class,
                []
            ) === false) {
========
        if ($container->getParameter(ConfigParam::OverrideApiPlatformListener->value) === false) {
>>>>>>>> refs/heads/6.x:packages/EasyErrorHandler/bundle/CompilerPass/ApiPlatformCompilerPass.php
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
