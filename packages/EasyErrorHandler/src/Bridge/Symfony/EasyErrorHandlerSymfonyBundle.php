<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony;

use EonX\EasyErrorHandler\Bridge\Symfony\DependencyInjection\Compiler\ApiPlatformCompilerPass;
use EonX\EasyErrorHandler\Bridge\Symfony\DependencyInjection\Compiler\ErrorHandlerCompilerPass;
use EonX\EasyErrorHandler\Bridge\Symfony\DependencyInjection\Compiler\ErrorRendererCompilerPass;
use EonX\EasyErrorHandler\Bridge\Symfony\DependencyInjection\EasyErrorHandlerExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyErrorHandlerSymfonyBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container
            ->addCompilerPass(new ApiPlatformCompilerPass())
            ->addCompilerPass(new ErrorHandlerCompilerPass())
            ->addCompilerPass(new ErrorRendererCompilerPass());
    }

    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyErrorHandlerExtension();
    }
}
