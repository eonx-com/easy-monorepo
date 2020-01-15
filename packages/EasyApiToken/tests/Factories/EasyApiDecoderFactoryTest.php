<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Factories;

use EonX\EasyApiToken\Decoders\ApiKeyAsBasicAuthUsernameDecoder;
use EonX\EasyApiToken\Decoders\BasicAuthDecoder;
use EonX\EasyApiToken\Decoders\ChainReturnFirstTokenDecoder;
use EonX\EasyApiToken\Decoders\JwtTokenDecoder;
use EonX\EasyApiToken\Decoders\JwtTokenInQueryDecoder;
use EonX\EasyApiToken\Exceptions\InvalidConfigurationException;
use EonX\EasyApiToken\External\Auth0JwtDriver;
use EonX\EasyApiToken\External\FirebaseJwtDriver;
use EonX\EasyApiToken\Factories\Decoders\BasicAuthDecoderFactory;
use EonX\EasyApiToken\Factories\EasyApiTokenDecoderFactory;
use EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use EonX\EasyApiToken\Tests\AbstractTestCase;
use EonX\EasyApiToken\Tokens\Factories\JwtEasyApiTokenFactory;
use Laravel\Lumen\Application;

/**
 * @covers \EonX\EasyApiToken\Factories\EasyApiTokenDecoderFactory
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
     * Get a list of errors caused by Invalid configurations.
     *
     * @return iterable<mixed> List of Configuration array, key to request, and expected error message.
     */
    public function getBrokenConfigurations(): iterable
    {
        yield 'Empty configuration' => [
            [],
            'nothing',
            'No decoder configured for key: "nothing".'
        ];

        yield 'Error is thrown when a non-existent key is requested.' => [
            ['onething' => ['type' => 'basic']],
            'some_other_thing',
            'No decoder configured for key: "some_other_thing".'
        ];

        yield 'Error because no type set and no default factories for decoder' => [
            ['fake-basic' => []],
            'fake-basic',
            'No "type" or default factory configured for decoder "fake-basic".'
        ];

        yield 'Test that an error is thrown when a non-existent decoder type is configured.' => [
            ['xxx' => ['type' => 'yyy', 'driver' => 'auth0', 'options' => []]],
            'xxx',
            'Unable to instantiate the factory "yyy" for decoder "xxx".'
        ];

        yield 'Expect chain driver with no list to return error.' => [
            ['chain-thing' => ['type' => 'chain']],
            'chain-thing',
            '"list" is required and must be an array for decoder "chain-thing".'
        ];

        yield 'Expect error for missing options supplied for JWT driver.' => [
            ['rad' => ['type' => 'jwt-header', 'driver' => 'auth0']],
            'rad',
            '"options" is required and must be an array for decoder "rad".'
        ];

        yield 'Expect error for invalid jwt driver.' => [
            ['foobar' => ['type' => 'jwt-header', 'driver' => 'GOOGLE', 'options' => ['not-empty']]],
            'foobar',
            '"driver" value "GOOGLE" is invalid. Valid drivers: ["auth0", "firebase"].'
        ];

        yield 'Expect error for missing jwt driver' => [
            ['something' => ['type' => 'jwt-header', 'options' => []]],
            'something',
            '"driver" is required and must be a string for decoder "something".'
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
                        'allowed_algos' => ['HS256', 'RS256']
                    ]
                ]
            ],
            'foobar',
            '"param" is required and must be an string for decoder "foobar".'
        ];
    }

    /**
     * Get a list builds for chain decoder.
     *
     * @return iterable<mixed>
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidArgumentException
     */
    public function getChainBuilds(): iterable
    {
        $config = [
            'chain-key' => [
                'type' => 'chain',
                'list' => [
                    'api',
                    'pass'
                ]
            ],
            'api' => ['type' => 'user-apikey'],
            'pass' => ['type' => 'basic']
        ];

        yield 'Build API Chain' => [
            $config,
            'chain-key',
            new ChainReturnFirstTokenDecoder([
                new ApiKeyAsBasicAuthUsernameDecoder(),
                new BasicAuthDecoder()
            ])
        ];
    }

    /**
     * Get a list of builds for jwt decoders.
     *
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
                    'allowed_algos' => ['HS256', 'RS256']
                ]
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
                    'public_key' => 'somepublickeystring'
                ]
            ],
            'jwt-by-header-firebase' => [
                'type' => 'jwt-header',
                'driver' => 'firebase',
                'options' => [
                    'algo' => 'HS256',
                    'allowed_algos' => ['HS256', 'RS256'],
                    'leeway' => 15,
                    'private_key' => 'someprivatekeystring',
                    'public_key' => 'somepublickeystring'
                ]
            ],
            'jwt-by-parameter-auth0' => [
                'type' => 'jwt-param',
                'driver' => 'auth0',
                'options' => [
                    'allowed_algos' => ['HS256', 'RS256'],
                    'authorized_iss' => ['xyz.auth0', 'abc.goog'],
                    'param' => 'authParam',
                    'private_key' => 'someprivatekeystring',
                    'valid_audiences' => ['id1', 'id2']
                ]
            ]
        ];

        yield 'Jwt Header' => [
            $config,
            'jwt-by-header',
            new JwtTokenDecoder(
                new JwtEasyApiTokenFactory(
                    new Auth0JwtDriver(
                        ['id1', 'id2'],
                        ['xyz.auth0', 'abc.goog'],
                        'someprivatekeystring',
                        'id1',
                        ['HS256', 'RS256']
                    )
                )
            )
        ];

        yield 'Jwt Parameter' => [
            $config,
            'jwt-by-parameter',
            new JwtTokenInQueryDecoder(
                new JwtEasyApiTokenFactory(
                    new FirebaseJwtDriver(
                        'HS256',
                        'somepublickeystring',
                        'someprivatekeystring',
                        ['HS256', 'RS256'],
                        15
                    )
                ),
                'authParam'
            )
        ];

        yield 'Jwt Header with Firebase' => [
            $config,
            'jwt-by-header-firebase',
            new JwtTokenDecoder(
                new JwtEasyApiTokenFactory(
                    new FirebaseJwtDriver(
                        'HS256',
                        'somepublickeystring',
                        'someprivatekeystring',
                        ['HS256', 'RS256'],
                        15
                    )
                )
            )
        ];

        yield 'Jwt Parameter with Auth0' => [
            $config,
            'jwt-by-parameter-auth0',
            new JwtTokenInQueryDecoder(
                new JwtEasyApiTokenFactory(
                    new Auth0JwtDriver(
                        ['id1', 'id2'],
                        ['xyz.auth0', 'abc.goog'],
                        'someprivatekeystring',
                        'id1',
                        ['HS256', 'RS256']
                    )
                ),
                'authParam'
            )
        ];
    }

    /**
     * Get a list of builds for simple decoders.
     *
     * @return iterable<mixed>
     */
    public function getSimpleBuilds(): iterable
    {
        yield 'Simple API Key' => [
            ['apiconfig' => ['type' => 'user-apikey']],
            'apiconfig',
            new ApiKeyAsBasicAuthUsernameDecoder()
        ];

        yield 'Simple Basic Auth decoder' => [
            ['something' => ['type' => 'basic']],
            'something',
            new BasicAuthDecoder()
        ];

        yield 'Simple Basic Auth decoder using default factory' => [
            ['basic' => null],
            'basic',
            new BasicAuthDecoder()
        ];
    }

    /**
     * Test that the requested object graph is built as expected.
     *
     * @param mixed[] $config Config array to build factory with.
     * @param string $key Key of configuration to request.
     * @param \EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface $expected Expected decoder object.
     *
     * @return void
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     *
     * @dataProvider getSimpleBuilds
     * @dataProvider getJwtBuilds
     * @dataProvider getChainBuilds
     */
    public function testBuild(array $config, string $key, EasyApiTokenDecoderInterface $expected): void
    {
        $factory = new EasyApiTokenDecoderFactory($config);

        $actual = $factory->build($key);
        $second = $factory->build($key);

        $this->assertEquals($expected, $actual);
        $this->assertEquals(\spl_object_hash($actual), \spl_object_hash($second));
    }

    /**
     * Test that the factory handles the container exceptions and returns its own one.
     *
     * @return void
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    public function testBuildContainerThrows(): void
    {
        $container = new class extends Application {
            /**
             * @noinspection PhpMissingParentCallCommonInspection
             *
             * {@inheritdoc}
             */
            public function has($id)
            {
                throw new \RuntimeException('runtime problems');
            }
        };

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('runtime problems');

        $factory = new EasyApiTokenDecoderFactory(['basic' => []]);
        $factory->setContainer($container);

        $factory->build('basic');
    }

    /**
     * Test that the requested object graph is built as expected.
     *
     * @param mixed[] $config Config array to build factory with.
     * @param string $key Key of configuration to request.
     * @param \EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface $expected Expected decoder object.
     *
     * @return void
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     *
     * @dataProvider getSimpleBuilds
     * @dataProvider getJwtBuilds
     * @dataProvider getChainBuilds
     */
    public function testBuildWithContainer(array $config, string $key, EasyApiTokenDecoderInterface $expected): void
    {
        $container = new Application();
        $container->bind(BasicAuthDecoderFactory::class, BasicAuthDecoderFactory::class);

        $factory = new EasyApiTokenDecoderFactory($config);
        $factory->setContainer($container);

        $actual = $factory->build($key);
        $second = $factory->build($key);

        $this->assertEquals($expected, $actual);
        $this->assertEquals(\spl_object_hash($actual), \spl_object_hash($second));
    }

    /**
     * Test Exceptions for building invalid configurations.
     *
     * @param mixed[] $config Config array to build factory with.
     * @param string $key Key of configuration to request.
     * @param string $expectedError Expected exception message.
     *
     * @return void
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     *
     * @dataProvider getBrokenConfigurations
     */
    public function testInvalidConfigurationErrors(array $config, string $key, string $expectedError): void
    {
        $factory = new EasyApiTokenDecoderFactory($config);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage($expectedError);

        $factory->build($key);
    }
}
