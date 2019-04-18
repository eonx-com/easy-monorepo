<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Tests\Decoders;

use StepTheFkUp\EasyApiToken\Decoders\ApiKeyAsBasicAuthUsernameDecoder;
use StepTheFkUp\EasyApiToken\Decoders\BasicAuthDecoder;
use StepTheFkUp\EasyApiToken\Decoders\ChainReturnFirstTokenDecoder;
use StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException;
use StepTheFkUp\EasyApiToken\Interfaces\Tokens\ApiKeyEasyApiTokenInterface;
use StepTheFkUp\EasyApiToken\Interfaces\Tokens\BasicAuthEasyApiTokenInterface;
use StepTheFkUp\EasyApiToken\Tests\AbstractTestCase;

final class ChainReturnFirstTokenDecoderTest extends AbstractTestCase
{
    /**
     * ChainReturnFirstTokenDecoder should return api key token.
     *
     * @return void
     *
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException
     */
    public function testChainFirstApiKeyTokenSuccessfully(): void
    {
        /** @var \StepTheFkUp\EasyApiToken\Interfaces\Tokens\ApiKeyEasyApiTokenInterface $token */
        $token = $this->createChainReturnFirstTokenDecoder()->decode($this->createServerRequest([
            'HTTP_AUTHORIZATION' => 'Basic ' . \base64_encode('api-key: ')
        ]));

        self::assertInstanceOf(ApiKeyEasyApiTokenInterface::class, $token);
        self::assertEquals('api-key', $token->getPayload()['api_key']);
    }

    /**
     * ChainReturnFirstTokenDecoder should return basic auth token.
     *
     * @return void
     *
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException
     */
    public function testChainFirstBasicAuthTokenSuccessfully(): void
    {
        /** @var \StepTheFkUp\EasyApiToken\Interfaces\Tokens\BasicAuthEasyApiTokenInterface $token */
        $token = $this->createChainReturnFirstTokenDecoder()->decode($this->createServerRequest([
            'HTTP_AUTHORIZATION' => 'Basic ' . \base64_encode('username:password')
        ]));

        self::assertInstanceOf(BasicAuthEasyApiTokenInterface::class, $token);
        self::assertEquals('username', $token->getPayload()['username']);
        self::assertEquals('password', $token->getPayload()['password']);
    }

    /**
     * ChainReturnFirstTokenDecoder should return null if no decoder could decode token.
     *
     * @return void
     *
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException
     */
    public function testChainFirstNullIfNoDecoderCouldDecoderToken(): void
    {
        self::assertNull($this->createChainReturnFirstTokenDecoder()->decode($this->createServerRequest()));
    }

    /**
     * ChainReturnFirstTokenDecoder should throw exception if array of decoders is empty.
     *
     * @return void
     *
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException
     */
    public function testEmptyDecodersArrayException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ChainReturnFirstTokenDecoder([]);
    }

    /**
     * ChainReturnFirstTokenDecoder should throw exception if one of the decoders doesn't implement the right interface.
     *
     * @return void
     *
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException
     */
    public function testInvalidDecoderException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ChainReturnFirstTokenDecoder(['invalid-decoder']);
    }

    /**
     * Create ChainReturnFirstTokenDecoder.
     *
     * @return \StepTheFkUp\EasyApiToken\Decoders\ChainReturnFirstTokenDecoder
     *
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException
     */
    private function createChainReturnFirstTokenDecoder(): ChainReturnFirstTokenDecoder
    {
        return new ChainReturnFirstTokenDecoder([new BasicAuthDecoder(), new ApiKeyAsBasicAuthUsernameDecoder()]);
    }
}

\class_alias(
    ChainReturnFirstTokenDecoderTest::class,
    'LoyaltyCorp\EasyApiToken\Tests\Decoders\ChainReturnFirstTokenDecoderTest',
    false
);
