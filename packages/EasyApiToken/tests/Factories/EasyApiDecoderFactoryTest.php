<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Tests\Factories;

use LoyaltyCorp\EasyApiToken\Decoders\BasicAuthDecoder;
use LoyaltyCorp\EasyApiToken\Decoders\ChainReturnFirstTokenDecoder;
use LoyaltyCorp\EasyApiToken\Decoders\JwtTokenDecoder;
use LoyaltyCorp\EasyApiToken\Decoders\JwtTokenInQueryDecoder;
use LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException;
use LoyaltyCorp\EasyApiToken\External\Auth0JwtDriver;
use LoyaltyCorp\EasyApiToken\External\FirebaseJwtDriver;
use LoyaltyCorp\EasyApiToken\Factories\EasyApiTokenDecoderFactory;
use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use LoyaltyCorp\EasyApiToken\Tests\AbstractTestCase;
use LoyaltyCorp\EasyApiToken\Tokens\Factories\JwtEasyApiTokenFactory;
use StepTheFkUp\EasyApiToken\Decoders\ApiKeyAsBasicAuthUsernameDecoder;

/**
 * @covers \LoyaltyCorp\EasyApiToken\Factories\EasyApiTokenDecoderFactory
 */
final class EasyApiDecoderFactoryTest extends AbstractTestCase
{
    /**
     * Get a list of errors caused by Invalid configurations.
     *
     * @return iterable List of Configuration array, key to request, and expected error message.
     */
    public function getBrokenConfigurations(): iterable
    {
        yield 'Empty configuration' => [
            [],
            'nothing',
            'Could not find a valid configuration.'
        ];

        yield 'Error is thrown when a non-existent key is requested.' => [
            ['onething' => ['type' => 'basic']],
            'some_other_thing',
            'Could not find EasyApiToken for key: some_other_thing.'
        ];

        yield 'Test that an error is thrown when a non-existent decoder type is configured.' => [
            ['xxx' => ['type' => 'yyy', 'driver' => 'auth0', 'options' => []]],
            'xxx',
            'Invalid EasyApiToken decoder type: yyy configured for key: xxx.'
        ];

        yield 'Expect chain driver with no list to return error.' => [
            ['chain-thing' => ['type' => 'chain']],
            'chain-thing',
            'EasyApiToken decoder: chain-thing is missing a required list option.'
        ];

        yield 'Expect error for missing options supplied for JWT driver.' => [
            ['rad' => ['type' => 'jwt-header', 'driver' => 'auth0']],
            'rad',
            'Missing options array for EasyApiToken decoder: rad.'
        ];

        yield 'Expect error for invalid jwt driver.' => [
            ['foobar' => ['type' => 'jwt-header', 'driver' => 'GOOGLE', 'options' => []]],
            'foobar',
            'Invalid JWT decoder driver: GOOGLE.'
        ];

        yield 'Expect error for missing jwt driver' => [
            ['something' => ['type' => 'jwt-header', 'options' => []]],
            'something',
            'EasyApiToken decoder: something is missing a driver key.'
        ];
    }

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
    }

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
                    'public_key' => 'somepublickeystring',
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
                    'public_key' => 'somepublickeystring',
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
     * @return iterable
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException
     */
    public function getChainBuilds(): iterable
    {
        $config = [
            'chain-key' => [
                'type' => 'chain',
                'list' => [
                    'api',
                    'pass'
                ],
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
     * Test that the requested object graph is built as expected.
     *
     * @param array $config Config array to build factory with.
     * @param string $key Key of configuration to request.
     * @param \LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface $expected Expected decoder object.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException
     *
     * @dataProvider getSimpleBuilds
     * @dataProvider getJwtBuilds
     * @dataProvider getChainBuilds
     */
    public function testBuild(array $config, string $key, EasyApiTokenDecoderInterface $expected): void
    {
        $factory = new EasyApiTokenDecoderFactory($config);

        $actual = $factory->build($key);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test Exceptions for building invalid configurations.
     *
     * @param array $config Config array to build factory with.
     * @param string $key Key of configuration to request.
     * @param string $expectedError Expected exception message.
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException
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

\class_alias(
    EasyApiDecoderFactoryTest::class,
    'StepTheFkUp\EasyApiToken\Tests\Factories\EasyApiDecoderFactoryTest',
    false
);
