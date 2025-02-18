<?php
declare(strict_types=1);

namespace EonX\EasyTest\Bundle\CompilerPass;

use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Common\ErrorHandler\TraceableErrorHandler;
use EonX\EasyErrorHandler\Common\ErrorHandler\TraceableErrorHandlerInterface;
use EonX\EasyTest\EasyErrorHandler\ErrorHandler\TraceableErrorHandlerStub;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterTraceableErrorHandlerStubCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition(TraceableErrorHandlerInterface::class) === false) {
            $container
                ->register(TraceableErrorHandlerInterface::class, TraceableErrorHandler::class)
                ->setDecoratedService(ErrorHandlerInterface::class)
                ->addArgument(new Reference(\sprintf('%s.inner', TraceableErrorHandlerInterface::class)));
        }

        $container
            ->register(TraceableErrorHandlerStub::class, TraceableErrorHandlerStub::class)
            ->setDecoratedService(TraceableErrorHandlerInterface::class)
            ->addArgument(new Reference(\sprintf('%s.inner', TraceableErrorHandlerStub::class)));
    }
}
