<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Bridge\Symfony;

use EonX\EasyActivity\Interfaces\ActivitySubjectResolverInterface;
use EonX\EasyActivity\Interfaces\ActorResolverInterface;
use EonX\EasyActivity\Resolvers\DefaultActorResolver;
use EonX\EasyActivity\Tests\Fixtures\Article;
use EonX\EasyActivity\Tests\Fixtures\Author;
use EonX\EasyActivity\Tests\Fixtures\Comment;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface;
use EonX\EasyDoctrine\Interfaces\EntityEventSubscriberInterface;
use EonX\EasyDoctrine\Subscribers\EntityEventSubscriber;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\Filesystem\Filesystem;

final class EasyActivitySymfonyBundleTest extends AbstractSymfonyTestCase
{
    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $var = __DIR__ . '/../../../var';

        if ($fs->exists($var)) {
            $fs->remove($var);
        }

        parent::tearDown();
    }

    /**
     * @see testInvalidEasyActivityConfig
     */
    public static function providerInvalidEasyConfigs(): iterable
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
     * @see testValidEasyActivityConfig
     */
    public static function providerValidEasyConfigs(): iterable
    {
        yield 'default config' => [
            'configName' => 'easy_activity_valid_default.yaml',
            'subjects' => [
                Article::class => [
                    'type' => 'article',
                    'disallowed_properties' => ['updatedAt', 'createdAt'],
                    'allowed_properties' => [],
                    'nested_object_allowed_properties' => [],
                ],
            ],
        ];

        yield 'minimal config' => [
            'configName' => 'easy_activity_valid_minimal.yaml',
            'subjects' => [
                Article::class => [
                    'disallowed_properties' => [],
                    'allowed_properties' => [],
                    'nested_object_allowed_properties' => [],
                ],
            ],
        ];

        yield 'empty config' => [
            'configName' => 'easy_activity_valid_empty.yaml',
            'subjects' => [],
        ];

        yield 'maximal config' => [
            'configName' => 'easy_activity_valid_maximal.yaml',
            'subjects' => [
                Article::class => [
                    'allowed_properties' => [
                        'comments',
                        'author' => ['name', 'position'],
                    ],
                    'disallowed_properties' => ['content'],
                    'nested_object_allowed_properties' => [
                        Author::class => ['position'],
                    ],
                ],
            ],
        ];
    }

    public function testEasyDoctrineEntitiesOverride(): void
    {
        $container = $this->getKernel([__DIR__ . '/Fixtures/easy_activity_with_doctrine_entities.yaml'])
            ->getContainer();

        /** @var \EonX\EasyDoctrine\Subscribers\EntityEventSubscriber $subscriber */
        $subscriber = $container->get(EntityEventSubscriberInterface::class);
        $entities = $this->getPrivatePropertyValue($subscriber, 'acceptableEntities');
        self::assertEqualsCanonicalizing([Author::class, Comment::class, Article::class], $entities);
        self::assertInstanceOf(EntityEventSubscriber::class, $subscriber);
    }

    #[DataProvider('providerInvalidEasyConfigs')]
    public function testInvalidEasyActivityConfig(string $configName, string $expectedExceptionClass): void
    {
        $this->safeCall(function () use ($configName): void {
            $this->getKernel([__DIR__ . '/Fixtures/' . $configName])->getContainer();
        });

        $this->assertThrownException($expectedExceptionClass, 0);
    }

    #[DataProvider('providerValidEasyConfigs')]
    public function testValidEasyActivityConfig(string $configName, array $subjects): void
    {
        $container = $this->getKernel([__DIR__ . '/Fixtures/' . $configName])->getContainer();
        /** @var \EonX\EasyActivity\Interfaces\ActivitySubjectResolverInterface $subjectResolver */
        $subjectResolver = $container->get(ActivitySubjectResolverInterface::class);

        self::assertInstanceOf(
            DefaultActorResolver::class,
            $container->get(ActorResolverInterface::class)
        );
        self::assertTrue($container->has(DeferredEntityEventDispatcherInterface::class));
        self::assertEquals($subjects, $this->getPrivatePropertyValue($subjectResolver, 'subjects'));
    }
}
