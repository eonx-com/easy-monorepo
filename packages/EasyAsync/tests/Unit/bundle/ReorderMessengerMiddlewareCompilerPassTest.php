<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Unit\Bundle;

use EonX\EasyAsync\Bundle\CompilerPass\ReorderMessengerMiddlewareCompilerPass;
use EonX\EasyAsync\Bundle\Enum\ConfigParam;
use EonX\EasyAsync\Messenger\Middleware\DoctrineManagersClearMiddleware;
use EonX\EasyAsync\Messenger\Middleware\DoctrineManagersSanityCheckMiddleware;
use EonX\EasyAsync\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class ReorderMessengerMiddlewareCompilerPassTest extends AbstractUnitTestCase
{
    public function testProcessAddsEnabledMiddlewareAtTheStartOfTheBus(): void
    {
        $container = $this->createContainerBuilder();
        $container->setDefinition(DoctrineManagersSanityCheckMiddleware::class, new Definition());
        $container->setDefinition(DoctrineManagersClearMiddleware::class, new Definition());
        $container->setDefinition(
            'messenger.bus.default',
            $this->createBusDefinition([
                new Reference('middleware.one'),
                new Reference('middleware.two'),
            ])
        );

        $this->process($container);

        self::assertSame(
            [
                DoctrineManagersSanityCheckMiddleware::class,
                DoctrineManagersClearMiddleware::class,
                'middleware.one',
                'middleware.two',
            ],
            $this->middlewareIds($container)
        );
    }

    public function testProcessAddsOnlyRegisteredMiddleware(): void
    {
        $container = $this->createContainerBuilder();
        $container->setDefinition(DoctrineManagersClearMiddleware::class, new Definition());
        $container->setDefinition(
            'messenger.bus.default',
            $this->createBusDefinition([
                new Reference('middleware.one'),
            ])
        );

        $this->process($container);

        self::assertSame(
            [
                DoctrineManagersClearMiddleware::class,
                'middleware.one',
            ],
            $this->middlewareIds($container)
        );
    }

    public function testProcessRemovesExistingEasyAsyncMiddlewareDuplicates(): void
    {
        $container = $this->createContainerBuilder();
        $container->setDefinition(DoctrineManagersSanityCheckMiddleware::class, new Definition());
        $container->setDefinition(DoctrineManagersClearMiddleware::class, new Definition());
        $container->setDefinition(
            'messenger.bus.default',
            $this->createBusDefinition([
                new Reference('middleware.one'),
                new Reference(DoctrineManagersClearMiddleware::class),
                new Reference('middleware.two'),
                new Reference(DoctrineManagersSanityCheckMiddleware::class),
            ])
        );

        $this->process($container);

        self::assertSame(
            [
                DoctrineManagersSanityCheckMiddleware::class,
                DoctrineManagersClearMiddleware::class,
                'middleware.one',
                'middleware.two',
            ],
            $this->middlewareIds($container)
        );
    }

    private function createBusDefinition(array $middleware): Definition
    {
        $definition = new Definition();
        $definition->addTag('messenger.bus');
        $definition->setArgument(0, new IteratorArgument($middleware));

        return $definition;
    }

    private function createContainerBuilder(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter(ConfigParam::MessengerWorkerMiddlewareEnabled->value, true);

        return $container;
    }

    private function middlewareIds(ContainerBuilder $container): array
    {
        $middleware = $container
            ->getDefinition('messenger.bus.default')
            ->getArgument(0);
        self::assertInstanceOf(IteratorArgument::class, $middleware);

        return \array_map(
            static fn (Reference $reference): string => (string)$reference,
            $middleware->getValues()
        );
    }

    private function process(ContainerBuilder $container): void
    {
        (new ReorderMessengerMiddlewareCompilerPass())->process($container);
    }
}
