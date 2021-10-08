<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Bridge\Symfony;

use EonX\EasyActivity\DefaultActorResolver;
use EonX\EasyActivity\Interfaces\ActorResolverInterface;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface;

final class EasyActivitySymfonyBundleTest extends AbstractSymfonyTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testEasyActivityConfig
     */
    public function providerEasyConfigs(): iterable
    {
        yield 'default config' => ['easy_activity_default.yaml'];

        yield 'minimal config' => ['easy_activity_default.yaml'];

        yield 'without subjects config' => ['easy_activity_without_subjects.yaml'];
    }

    /**
     * @dataProvider providerEasyConfigs
     */
    public function testEasyActivityConfig(string $configName): void
    {
        $container = $this->getKernel([__DIR__ . '/Fixtures/' . $configName])->getContainer();

        self::assertInstanceOf(
            DefaultActorResolver::class,
            $container->get(ActorResolverInterface::class)
        );
        self::assertTrue($container->has(DeferredEntityEventDispatcherInterface::class));
    }
}
