<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Factories;

use Auth0\SDK\Helpers\Cache\FileSystemCacheHandler;
use EonX\EasyApiToken\Decoders\ApiKeyAsBasicAuthUsernameDecoder;
use EonX\EasyApiToken\Decoders\BasicAuthDecoder;
use EonX\EasyApiToken\Decoders\ChainDecoder;
use EonX\EasyApiToken\Decoders\JwtTokenDecoder;
use EonX\EasyApiToken\Decoders\JwtTokenInQueryDecoder;
use EonX\EasyApiToken\Exceptions\InvalidConfigurationException;
use EonX\EasyApiToken\Exceptions\InvalidDefaultDecoderException;
use EonX\EasyApiToken\External\Auth0JwtDriver;
use EonX\EasyApiToken\External\FirebaseJwtDriver;
use EonX\EasyApiToken\Factories\ApiTokenDecoderFactory;
use EonX\EasyApiToken\Factories\Decoders\BasicAuthDecoderFactory;
use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use EonX\EasyApiToken\Providers\FromConfigDecoderProvider;
use EonX\EasyApiToken\Tests\AbstractTestCase;
use EonX\EasyApiToken\Tokens\Factories\JwtFactory;
use Laravel\Lumen\Application;

/**
 * @covers \EonX\EasyApiToken\Factories\ApiTokenDecoderFactory
 * @covers \EonX\EasyApiToken\Factories\Decoders\AbstractJwtTokenDecoderFactory
 * @covers \EonX\EasyApiToken\Factories\Decoders\ApiKeyAsBasicAuthUsernameDecoderFactory
 * @covers \EonX\EasyApiToken\Factories\Decoders\BasicAuthDecoderFactory
 * @covers \EonX\EasyApiToken\Factories\Decoders\ChainReturnFirstTokenDecoderFactory
 * @covers \EonX\EasyApiToken\Factories\Decoders\JwtTokenDecoderFactory
 * @covers \EonX\EasyApiToken\Factories\Decoders\JwtTokenInQueryDecoderFactory
 */
final class EasyApiDecoderFactoryTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function getBrokenConfigurations(): iterable
    {
        yield 'Empty configuration' => [[], 'nothing', 'No decoders configured'];

        yield 'Error is thrown when a non-existent key is requested.' => [
            [
                'onething' => [
                    'type' => 'basic',
                ],
            ],
            'some_other_thing',
            'No decoder configured for key: "some_other_thing".',
        ];

        yield 'Error because no type set and no default factories for decoder' => [
            [
                'fake-basic' => [],
            ],
            'fake-basic',
            'No "type" or default factory configured for decoder "fake-basic".',
        ];

        yield 'Test that an error is thrown when a non-existent decoder type is configured.' => [
            [
                'xxx' => [
                    'type' => 'yyy',
                    'driver' => 'auth0',
                    'options' => [],
                ],
            ],
            'xxx',
            'Unable to instantiate the factory "yyy" for decoder "xxx".',
        ];

        yield 'Expect chain driver with no list to return error.' => [
            [
                'chain-thing' => [
                    'type' => 'chain',
                ],
            ],
            'chain-thing',
            '"list" is required and must be an array for decoder "chain-thing".',
        ];

        yield 'Expect error for missing options supplied for JWT driver.' => [
            [
                'rad' => [
                    'type' => 'jwt-header',
                    'driver' => 'auth0',
                ],
            ],
            'rad',
            '"options" is required and must be an array for decoder "rad".',
        ];

        yield 'Expect error for invalid jwt driver.' => [
            [
                'foobar' => [
                    'type' => 'jwt-header',
                    'driver' => 'GOOGLE',
                    'options' => ['not-empty'],
                ],
            ],
            'foobar',
            '"driver" value "GOOGLE" is invalid. Valid drivers: ["auth0", "firebase"].',
        ];

        yield 'Expect error for missing jwt driver' => [
            [
                'something' => [
                    'type' => 'jwt-header',
                    'options' => [],
                ],
            ],
            'something',
            '"driver" is required and must be a string for decoder "something".',
        ];

        yield 'Expect error for missing param' => [
            [
                'foobar' => [
                    'type' => 'jwt-param',
                    'driver' => 'auth0',
                    'options' => [
                        'valid_audiences' => ['id1', 'id2'],
                        'authorized_iss' => ['xyz.auth0', 'abc.goog'],
                        'private_key' => 'someprivatekeystring',
                        'allowed_algos' => ['HS256', 'RS256'],
                    ],
                ],
            ],
            'foobar',
            '"param" is required and must be an string for decoder "foobar".',
        ];
    }

    /**
     * @return iterable<mixed>
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidArgumentException
     */
    public function getChainBuilds(): iterable
    {
        $config = [
            'chain-key' => [
                'type' => 'chain',
                'list' => ['api', 'pass'],
            ],
            'api' => [
                'type' => 'user-apikey',
            ],
            'pass' => [
                'type' => 'basic',
            ],
        ];

        yield 'Build API Chain' => [
            $config,
            'chain-key',
            new ChainDecoder([
                new ApiKeyAsBasicAuthUsernameDecoder('api'),
                new BasicAuthDecoder('pass'),
            ], 'chain-key'),
        ];
    }

    /**
     * @return iterable<mixed>
     */
    public function getJwtBuilds(): iterable
    {
        $config = [
            'jwt-by-header' => [
                'type' => 'jwt-header',
                'driver' => 'auth0',
                'options' => [
                    'valid_audiences' => ['id1', 'id2'],
                    'authorized_iss' => ['xyz.auth0', 'abc.goog'],
                    'private_key' => 'someprivatekeystring',
                    'allowed_algos' => ['HS256', 'RS256'],
                ],
            ],
            'jwt-by-parameter' => [
                'type' => 'jwt-param',
                'driver' => 'firebase',
                'options' => [
                    'algo' => 'HS256',
                    'allowed_algos' => ['HS256', 'RS256'],
                    'leeway' => 15,
                    'param' => 'authParam',
                    'private_key' => 'someprivatekeystring',
                    'public_key' => 'somepublickeystring',
                ],
            ],
            'jwt-by-header-firebase' => [
                'type' => 'jwt-header',
                'driver' => 'firebase',
                'options' => [
                    'algo' => 'HS256',
                    'allowed_algos' => ['HS256', 'RS256'],
                    'leeway' => 15,
                    'private_key' => 'someprivatekeystring',
                    'public_key' => 'somepublickeystring',
                ],
            ],
            'jwt-by-parameter-auth0' => [
                'type' => 'jwt-param',
                'driver' => 'auth0',
                'options' => [
                    'allowed_algos' => ['HS256', 'RS256'],
                    'authorized_iss' => ['xyz.auth0', 'abc.goog'],
                    'param' => 'authParam',
                    'private_key' => 'someprivatekeystring',
                    'valid_audiences' => ['id1', 'id2'],
                ],
            ],
            'jwt-by-parameter-auth0-with-cache' => [
                'type' => 'jwt-param',
                'driver' => 'auth0',
                'options' => [
                    'allowed_algos' => ['HS256', 'RS256'],
                    'authorized_iss' => ['xyz.auth0', 'abc.goog'],
                    'cache_path' => 'test/path',
                    'param' => 'authParam',
                    'private_key' => 'someprivatekeystring',
                    'valid_audiences' => ['id1', 'id2'],
                ],
            ],
        ];

        yield 'Jwt Header' => [
            $config,
            'jwt-by-header',
            new JwtTokenDecoder(
                new JwtFactory(
                    new Auth0JwtDriver(
                        ['id1', 'id2'],
                        ['xyz.auth0', 'abc.goog'],
                        'someprivatekeystring',
                        'id1',
                        ['HS256', 'RS256']
                    )
                ),
                null,
                'jwt-by-header'
            ),
        ];

        yield 'Jwt Parameter' => [
            $config,
            'jwt-by-parameter',
            new JwtTokenInQueryDecoder(
                new JwtFactory(
                    new FirebaseJwtDriver(
                        'HS256',
                        'somepublickeystring',
                        'someprivatekeystring',
                        ['HS256', 'RS256'],
                        15
                    )
                ),
                'authParam',
                'jwt-by-parameter'
            ),
        ];

        yield 'Jwt Header with Firebase' => [
            $config,
            'jwt-by-header-firebase',
            new JwtTokenDecoder(
                new JwtFactory(
                    new FirebaseJwtDriver(
                        'HS256',
                        'somepublickeystring',
                        'someprivatekeystring',
                        ['HS256', 'RS256'],
                        15
                    )
                ),
                null,
                'jwt-by-header-firebase'
            ),
        ];

        yield 'Jwt Parameter with Auth0' => [
            $config,
            'jwt-by-parameter-auth0',
            new JwtTokenInQueryDecoder(
                new JwtFactory(
                    new Auth0JwtDriver(
                        ['id1', 'id2'],
                        ['xyz.auth0', 'abc.goog'],
                        'someprivatekeystring',
                        'id1',
                        ['HS256', 'RS256']
                    )
                ),
                'authParam',
                'jwt-by-parameter-auth0'
            ),
        ];

        yield 'Jwt Parameter with Auth0, with cache' => [
            $config,
            'jwt-by-parameter-auth0-with-cache',
            new JwtTokenInQueryDecoder(
                new JwtFactory(
                    new Auth0JwtDriver(
                        ['id1', 'id2'],
                        ['xyz.auth0', 'abc.goog'],
                        'someprivatekeystring',
                        'id1',
                        ['HS256', 'RS256'],
                        new FileSystemCacheHandler('test/path')
                    )
                ),
                'authParam',
                'jwt-by-parameter-auth0-with-cache'
            ),
        ];
    }

    /**
     * @return iterable<mixed>
     */
    public function getSimpleBuilds(): iterable
    {
        yield 'Simple API Key' => [
            [
                'apiconfig' => [
                    'type' => 'user-apikey',
                ],
            ],
            'apiconfig',
            new ApiKeyAsBasicAuthUsernameDecoder('apiconfig'),
        ];

        yield 'Simple Basic Auth decoder' => [
            [
                'something' => [
                    'type' => 'basic',
                ],
            ],
            'something',
            new BasicAuthDecoder('something'),
        ];

        yield 'Simple Basic Auth decoder using default factory' => [
            ['basic' => null],
            'basic',
            new BasicAuthDecoder(),
        ];
    }

    /**
     * @param mixed[] $config Config array to build factory with.
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     *
     * @dataProvider getSimpleBuilds
     * @dataProvider getJwtBuilds
     * @dataProvider getChainBuilds
     */
    public function testBuild(array $config, string $key, ApiTokenDecoderInterface $expected): void
    {
        $factory = new ApiTokenDecoderFactory([new FromConfigDecoderProvider($config)]);

        $actual = $factory->build($key);
        $second = $factory->build($key);

        $this->assertEquals($expected, $actual);
        $this->assertEquals(\spl_object_hash($actual), \spl_object_hash($second));
    }

    public function testBuildContainerThrows(): void
    {
        $container = new class() extends Application {
            public function has($id): bool
            {
                throw new \RuntimeException('runtime problems');
            }
        };

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('runtime problems');

        $provider = new FromConfigDecoderProvider([
            'basic' => [],
        ]);
        $provider->setContainer($container);

        $factory = new ApiTokenDecoderFactory([$provider]);

        $factory->build('basic');
    }

    /**
     * @param mixed[] $config Config array to build factory with.
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     *
     * @dataProvider getSimpleBuilds
     * @dataProvider getJwtBuilds
     * @dataProvider getChainBuilds
     */
    public function testBuildWithContainer(array $config, string $key, ApiTokenDecoderInterface $expected): void
    {
        $container = new Application();
        $container->bind(BasicAuthDecoderFactory::class, BasicAuthDecoderFactory::class);

        $provider = new FromConfigDecoderProvider($config);
        $provider->setContainer($container);

        $factory = new ApiTokenDecoderFactory([$provider]);

        $actual = $factory->build($key);
        $second = $factory->build($key);

        $this->assertEquals($expected, $actual);
        $this->assertEquals(\spl_object_hash($actual), \spl_object_hash($second));
    }

    /**
     * @param mixed[] $config Config array to build factory with.
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     *
     * @dataProvider getBrokenConfigurations
     */
    public function testInvalidConfigurationErrors(array $config, string $key, string $expectedError): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage($expectedError);

        (new ApiTokenDecoderFactory([new FromConfigDecoderProvider($config)]))->build($key);
    }

    public function testNoDefaultDecoderSetException(): void
    {
        $this->expectException(InvalidDefaultDecoderException::class);
        $this->expectExceptionMessage('No default decoder set');

        (new ApiTokenDecoderFactory([]))->buildDefault();
    }
}
