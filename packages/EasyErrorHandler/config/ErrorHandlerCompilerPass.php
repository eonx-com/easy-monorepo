<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\DependencyInjection\Compiler;

use EonX\EasyErrorHandler\Bridge\Symfony\Interfaces\TraceableErrorHandlerInterface;
use EonX\EasyErrorHandler\Bridge\Symfony\TraceableErrorHandler;
use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class ErrorHandlerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasParameter('kernel.debug') && $container->getParameter('kernel.debug')) {
            $container
                ->register(TraceableErrorHandlerInterface::class, TraceableErrorHandler::class)
                ->setDecoratedService(ErrorHandlerInterface::class)
                ->addArgument(new Reference(\sprintf('%s.inner', TraceableErrorHandlerInterface::class)));
        }
    }
}
