<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tests\Decoders;

use StepTheFkUp\ApiToken\Decoders\ApiKeyAsBasicAuthUsernameDecoder;
use StepTheFkUp\ApiToken\Decoders\BasicAuthDecoder;
use StepTheFkUp\ApiToken\Decoders\ChainReturnFirstTokenDecoder;
use StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException;
use StepTheFkUp\ApiToken\Interfaces\Tokens\ApiKeyApiTokenInterface;
use StepTheFkUp\ApiToken\Interfaces\Tokens\BasicAuthApiTokenInterface;
use StepTheFkUp\ApiToken\Tests\AbstractTestCase;

final class ChainReturnFirstTokenDecoderTest extends AbstractTestCase
{
    /**
     * ChainReturnFirstTokenDecoder should return api key token.
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException
     */
    public function testChainFirstApiKeyTokenSuccessfully(): void
    {
        /** @var \StepTheFkUp\ApiToken\Interfaces\Tokens\ApiKeyApiTokenInterface $token */
        $token = $this->createChainReturnFirstTokenDecoder()->decode($this->createServerRequest([
            'HTTP_AUTHORIZATION' => 'Basic ' . \base64_encode('api-key: ')
        ]));

        self::assertInstanceOf(ApiKeyApiTokenInterface::class, $token);
        self::assertEquals('api-key', $token->getPayload()['api_key']);
    }

    /**
     * ChainReturnFirstTokenDecoder should return basic auth token.
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException
     */
    public function testChainFirstBasicAuthTokenSuccessfully(): void
    {
        /** @var \StepTheFkUp\ApiToken\Interfaces\Tokens\BasicAuthApiTokenInterface $token */
        $token = $this->createChainReturnFirstTokenDecoder()->decode($this->createServerRequest([
            'HTTP_AUTHORIZATION' => 'Basic ' . \base64_encode('username:password')
        ]));

        self::assertInstanceOf(BasicAuthApiTokenInterface::class, $token);
        self::assertEquals('username', $token->getPayload()['username']);
        self::assertEquals('password', $token->getPayload()['password']);
    }

    /**
     * ChainReturnFirstTokenDecoder should return null if no decoder could decode token.
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException
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
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException
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
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException
     */
    public function testInvalidDecoderException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ChainReturnFirstTokenDecoder(['invalid-decoder']);
    }

    /**
     * Create ChainReturnFirstTokenDecoder.
     *
     * @return \StepTheFkUp\ApiToken\Decoders\ChainReturnFirstTokenDecoder
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException
     */
    private function createChainReturnFirstTokenDecoder(): ChainReturnFirstTokenDecoder
    {
        return new ChainReturnFirstTokenDecoder([new BasicAuthDecoder(), new ApiKeyAsBasicAuthUsernameDecoder()]);
    }
}
