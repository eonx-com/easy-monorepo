<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Bridge\Symfony;

use EonX\EasyActivity\Interfaces\ActorResolverInterface;
use EonX\EasyActivity\Resolvers\DefaultActorResolver;
use EonX\EasyActivity\Tests\Fixtures\Article;
use EonX\EasyActivity\Tests\Fixtures\Author;
use EonX\EasyActivity\Tests\Fixtures\Comment;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface;
use EonX\EasyDoctrine\Subscribers\EntityEventSubscriber;
use EonX\EasyDoctrine\Subscribers\EntityEventSubscriberInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\Filesystem\Filesystem;

final class EasyActivitySymfonyBundleTest extends AbstractSymfonyTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testInvalidEasyActivityConfig
     */
    public function providerInvalidEasyConfigs(): iterable
    {
        yield 'invalid allowed_properties setting' => [
            'configName' => 'easy_activity_invalid_allowed_properties_setting.yaml',
            'expectedExceptionClass' => InvalidTypeException::class,
        ];

        yield 'invalid nested_object_allowed_properties setting' => [
            'configName' => 'easy_activity_invalid_nested_object_allowed_properties_setting.yaml',
            'expectedExceptionClass' => InvalidTypeException::class,
        ];

        yield 'invalid subject setting' => [
            'configName' => 'easy_activity_invalid_subject_setting.yaml',
            'expectedExceptionClass' => InvalidConfigurationException::class,
        ];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testValidEasyActivityConfig
     */
    public function providerValidEasyConfigs(): iterable
    {
        yield 'default config' => ['easy_activity_valid_default.yaml'];

        yield 'minimal config' => ['easy_activity_valid_minimal.yaml'];

        yield 'empty config' => ['easy_activity_valid_empty.yaml'];

        yield 'maximal config' => ['easy_activity_valid_maximal.yaml'];
    }

    public function testEasyDoctrineEntitiesOverride(): void
    {
        $container = $this->getKernel(
            [__DIR__ . '/Fixtures/easy_activity_with_doctrine_entities.yaml']
        )->getContainer();

        /** @var \EonX\EasyDoctrine\Subscribers\EntityEventSubscriber $subscriber */
        $subscriber = $container->get(EntityEventSubscriberInterface::class);
        $entities = $this->getPrivatePropertyValue($subscriber, 'acceptableEntities');
        self::assertEqualsCanonicalizing([Author::class, Comment::class, Article::class], $entities);
        self::assertInstanceOf(EntityEventSubscriber::class, $subscriber);
    }

    /**
     * @dataProvider providerInvalidEasyConfigs
     */
    public function testInvalidEasyActivityConfig(string $configName, string $expectedExceptionClass): void
    {
        $this->safeCall(function () use ($configName) {
            $this->getKernel([__DIR__ . '/Fixtures/' . $configName])->getContainer();
        });

        $this->assertThrownException($expectedExceptionClass, 0);
    }

    /**
     * @dataProvider providerValidEasyConfigs
     */
    public function testValidEasyActivityConfig(string $configName): void
    {
        $container = $this->getKernel([__DIR__ . '/Fixtures/' . $configName])->getContainer();

        self::assertInstanceOf(
            DefaultActorResolver::class,
            $container->get(ActorResolverInterface::class)
        );
        self::assertTrue($container->has(DeferredEntityEventDispatcherInterface::class));
    }

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $var = __DIR__ . '/../../../var';

        if ($fs->exists($var)) {
            $fs->remove($var);
        }

        parent::tearDown();
    }
}
