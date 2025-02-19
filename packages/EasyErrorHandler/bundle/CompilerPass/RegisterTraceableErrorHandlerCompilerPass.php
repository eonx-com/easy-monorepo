<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bundle\CompilerPass;

use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Common\ErrorHandler\TraceableErrorHandler;
use EonX\EasyErrorHandler\Common\ErrorHandler\TraceableErrorHandlerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterTraceableErrorHandlerCompilerPass implements CompilerPassInterface
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
