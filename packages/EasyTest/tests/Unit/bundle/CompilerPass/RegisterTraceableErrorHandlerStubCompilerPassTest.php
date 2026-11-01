<?php
declare(strict_types=1);

namespace EonX\EasyTest\Tests\Unit\Bundle\CompilerPass;

use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Common\ErrorHandler\TraceableErrorHandler;
use EonX\EasyErrorHandler\Common\ErrorHandler\TraceableErrorHandlerInterface;
use EonX\EasyTest\Bundle\CompilerPass\RegisterTraceableErrorHandlerStubCompilerPass;
use EonX\EasyTest\EasyErrorHandler\ErrorHandler\TraceableErrorHandlerStub;
use EonX\EasyTest\Tests\Unit\AbstractUnitTestCase;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class RegisterTraceableErrorHandlerStubCompilerPassTest extends AbstractUnitTestCase
{
    public function testProcessDecoratesExistingTraceableErrorHandler(): void
    {
        $container = new ContainerBuilder();
        $container->setDefinition(ErrorHandlerInterface::class, new Definition(stdClass::class));
        $container->setDefinition(TraceableErrorHandlerInterface::class, new Definition(stdClass::class));

        (new RegisterTraceableErrorHandlerStubCompilerPass())->process($container);

        self::assertSame(
            stdClass::class,
            $container->getDefinition(TraceableErrorHandlerInterface::class)->getClass()
        );
        self::assertSame(
            [TraceableErrorHandlerInterface::class, null, 0],
            $container->getDefinition(TraceableErrorHandlerStub::class)->getDecoratedService()
        );
    }

    public function testProcessDoesNothingWithoutErrorHandler(): void
    {
        $container = new ContainerBuilder();

        (new RegisterTraceableErrorHandlerStubCompilerPass())->process($container);

        self::assertFalse($container->has(TraceableErrorHandlerInterface::class));
        self::assertFalse($container->has(TraceableErrorHandlerStub::class));
    }

    public function testProcessRegistersTraceableErrorHandlerAndStub(): void
    {
        $container = new ContainerBuilder();
        $container->setDefinition(ErrorHandlerInterface::class, new Definition(stdClass::class));

        (new RegisterTraceableErrorHandlerStubCompilerPass())->process($container);

        $traceableErrorHandlerDefinition = $container->getDefinition(TraceableErrorHandlerInterface::class);
        self::assertSame(TraceableErrorHandler::class, $traceableErrorHandlerDefinition->getClass());
        self::assertSame(
            [ErrorHandlerInterface::class, null, 0],
            $traceableErrorHandlerDefinition->getDecoratedService()
        );
        self::assertSame(
            [TraceableErrorHandlerInterface::class, null, 0],
            $container->getDefinition(TraceableErrorHandlerStub::class)->getDecoratedService()
        );
    }
}
