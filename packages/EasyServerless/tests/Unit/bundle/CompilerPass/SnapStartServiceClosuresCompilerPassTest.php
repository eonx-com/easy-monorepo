<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Bundle\CompilerPass;

use EonX\EasyServerless\Bundle\CompilerPass\SnapStartServiceClosuresCompilerPass;
use EonX\EasyServerless\SnapStart\Enum\SnapStartTag;
use EonX\EasyServerless\SnapStart\Subscriber\SnapStartLifecycleSubscriber;
use EonX\EasyServerless\Tests\Stub\SnapStart\Warmer\SnapStartWarmerStub;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class SnapStartServiceClosuresCompilerPassTest extends AbstractUnitTestCase
{
    public function testProcessDoesNothingWithoutSnapStartLifecycleSubscriber(): void
    {
        $container = new ContainerBuilder();
        $container->register('app.snap_start_warmer', SnapStartWarmerStub::class)
            ->addTag(SnapStartTag::Warmer->value);
        $expectedDefinitions = $container->getDefinitions();
        $sut = new SnapStartServiceClosuresCompilerPass();

        $sut->process($container);

        self::assertSame($expectedDefinitions, $container->getDefinitions());
    }

    public function testProcessReplacesTaggedIteratorsWithServiceClosuresKeyedByServiceId(): void
    {
        // As resolved and sorted by priority by ResolveTaggedIteratorArgumentPass
        $warmers = new TaggedIteratorArgument(SnapStartTag::Warmer->value);
        $warmers->setValues([new Reference('app.high_priority_warmer'), new Reference('app.low_priority_warmer')]);
        $afterRestoreHooks = new TaggedIteratorArgument(SnapStartTag::AfterRestoreHook->value);
        $afterRestoreHooks->setValues([new Reference('app.after_restore_hook')]);
        $container = new ContainerBuilder();
        $container->register(SnapStartLifecycleSubscriber::class, SnapStartLifecycleSubscriber::class)
            ->setArguments([$warmers, $afterRestoreHooks, new Reference('logger'), '/tmp/marker']);
        $expectedArguments = [
            [
                'app.high_priority_warmer' => new ServiceClosureArgument(new Reference('app.high_priority_warmer')),
                'app.low_priority_warmer' => new ServiceClosureArgument(new Reference('app.low_priority_warmer')),
            ],
            [
                'app.after_restore_hook' => new ServiceClosureArgument(new Reference('app.after_restore_hook')),
            ],
            new Reference('logger'),
            '/tmp/marker',
        ];
        // The order of the keys is not compared by assertEquals()
        $expectedWarmerIds = ['app.high_priority_warmer', 'app.low_priority_warmer'];
        $sut = new SnapStartServiceClosuresCompilerPass();

        $sut->process($container);

        $arguments = $container->getDefinition(SnapStartLifecycleSubscriber::class)
            ->getArguments();
        self::assertEquals($expectedArguments, $arguments);
        self::assertIsArray($arguments[0]);
        self::assertSame($expectedWarmerIds, \array_keys($arguments[0]));
    }
}
