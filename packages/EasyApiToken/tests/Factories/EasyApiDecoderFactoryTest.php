<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Tests\Factories;

use LoyaltyCorp\EasyApiToken\Decoders\BasicAuthDecoder;
use LoyaltyCorp\EasyApiToken\Decoders\JwtTokenDecoder;
use LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException;
use LoyaltyCorp\EasyApiToken\External\Auth0JwtDriver;
use LoyaltyCorp\EasyApiToken\Factories\EasyApiDecoderFactory;
use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use LoyaltyCorp\EasyApiToken\Tests\AbstractTestCase;
use LoyaltyCorp\EasyApiToken\Tokens\Factories\JwtEasyApiTokenFactory;
use StepTheFkUp\EasyApiToken\Decoders\ApiKeyAsBasicAuthUsernameDecoder;

/**
 * @covers \LoyaltyCorp\EasyApiToken\Factories\EasyApiDecoderFactory
 */
final class EasyApiDecoderFactoryTest extends AbstractTestCase
{
    /**
     * Test that an empty exception throws an error.
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    public function testNullCreation(): void
    {
        $factory = new EasyApiDecoderFactory([]);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Could not find a valid configuration.');

        $factory->build('nothing');
    }

    /**
     * Test that an error is thrown when a non-existent key is requested.
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    public function testNoSuchKey(): void
    {
        $factory = new EasyApiDecoderFactory(['onething' => ['type' => 'basic']]);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Could not find EasyApiToken for key: some_other_thing.');

        $factory->build('some_other_thing');
    }

    /**
     * Test that an error is thrown when a non-existent decoder type is configured.
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    public function testInvalidDriver(): void
    {
        $factory = new EasyApiDecoderFactory(['xxx' => ['type' => 'yyy']]);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid EasyApiToken decoder type: yyy configured for key: xxx.');

        $factory->build('xxx');
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
        yield 'Jwt Header' => [
            [
                'jwt' => [
                    'type' => 'jwt-header',
                    'driver' => 'auth0',
                    'options' => [
                        'valid_audiences' => ['id1', 'id2'],
                        'authorized_iss' => ['xyz.auth0', 'abc.goog'],
                        'private_key' => 'someprivatekeystring',
                        'allowed_algos' => ['HS256', 'RS256']
                    ]
                ]
            ],
            'jwt',
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
    }

    /**
     * Test that the requested object graph is built as expected.
     *
     * @param array $config
     * @param string $key
     * @param \LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface $expected
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException
     *
     * @dataProvider getSimpleBuilds
     * @dataProvider getJwtBuilds
     */
    public function testBuild(array $config, string $key, EasyApiTokenDecoderInterface $expected): void
    {
        $factory = new EasyApiDecoderFactory($config);

        $actual = $factory->build($key);

        $this->assertEquals($expected, $actual);
    }
}

\class_alias(
    EasyApiDecoderFactoryTest::class,
    'StepTheFkUp\EasyApiToken\Tests\Factories\EasyApiDecoderFactoryTest',
    false
);
