<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Tests\Factories;

use LoyaltyCorp\EasyApiToken\Decoders\BasicAuthDecoder;
use LoyaltyCorp\EasyApiToken\Decoders\JwtTokenDecoder;
use LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException;
use LoyaltyCorp\EasyApiToken\Factories\EasyApiDecoderFactory;
use LoyaltyCorp\EasyApiToken\Tests\AbstractTestCase;
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
     * Test that a basic driver is configured on request.
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    public function testBasicAuthCreation(): void
    {
        $factory = new EasyApiDecoderFactory(['something' => ['type' => 'basic']]);

        $actual = $factory->build('something');

        $this->assertInstanceOf(BasicAuthDecoder::class, $actual);
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

    /**
     * Test that an ApiKeyAsBasicAuthUsernameDecoder is created when requested.
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    public function testApiKeyDriver(): void
    {
        $factory = new EasyApiDecoderFactory(['apiconfig' => ['type' => 'user-apikey']]);

        $actual = $factory->build('apiconfig');

        $this->assertInstanceOf(ApiKeyAsBasicAuthUsernameDecoder::class, $actual);
    }

    /**
     * Test that an JwtTokenDecoder is created when requested.
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    public function testJwtWithAuth0Configuration(): void
    {
        $factory = new EasyApiDecoderFactory([
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
        ]);

        $actual = $factory->build('jwt');

        $this->assertInstanceOf(JwtTokenDecoder::class, $actual);
    }
}

\class_alias(
    EasyApiDecoderFactoryTest::class,
    'StepTheFkUp\EasyApiToken\Tests\Factories\EasyApiDecoderFactoryTest',
    false
);
