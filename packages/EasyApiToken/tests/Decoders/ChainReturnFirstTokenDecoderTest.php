<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Decoders;

use EonX\EasyApiToken\Decoders\ApiKeyAsBasicAuthUsernameDecoder;
use EonX\EasyApiToken\Decoders\BasicAuthDecoder;
use EonX\EasyApiToken\Decoders\ChainReturnFirstTokenDecoder;
use EonX\EasyApiToken\Exceptions\InvalidArgumentException;
use EonX\EasyApiToken\Interfaces\Tokens\ApiKeyInterface;
use EonX\EasyApiToken\Interfaces\Tokens\BasicAuthInterface;
use EonX\EasyApiToken\Tests\AbstractTestCase;

final class ChainReturnFirstTokenDecoderTest extends AbstractTestCase
{
    public function testChainFirstApiKeyTokenSuccessfully(): void
    {
        /** @var \EonX\EasyApiToken\Interfaces\Tokens\ApiKeyInterface $token */
        $token = $this->createChainReturnFirstTokenDecoder()->decode($this->createServerRequest([
            'HTTP_AUTHORIZATION' => 'Basic ' . \base64_encode('api-key: '),
        ]));

        self::assertInstanceOf(ApiKeyInterface::class, $token);
        self::assertEquals('api-key', $token->getPayload()['api_key']);
    }

    public function testChainFirstBasicAuthTokenSuccessfully(): void
    {
        /** @var \EonX\EasyApiToken\Interfaces\Tokens\BasicAuthInterface $token */
        $token = $this->createChainReturnFirstTokenDecoder()->decode($this->createServerRequest([
            'HTTP_AUTHORIZATION' => 'Basic ' . \base64_encode('username:password'),
        ]));

        self::assertInstanceOf(BasicAuthInterface::class, $token);
        self::assertEquals('username', $token->getPayload()['username']);
        self::assertEquals('password', $token->getPayload()['password']);
    }

    public function testChainFirstNullIfNoDecoderCouldDecoderToken(): void
    {
        self::assertNull($this->createChainReturnFirstTokenDecoder()->decode($this->createServerRequest()));
    }

    public function testEmptyDecodersArrayException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ChainReturnFirstTokenDecoder([]);
    }

    public function testInvalidDecoderException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ChainReturnFirstTokenDecoder(['invalid-decoder']);
    }

    private function createChainReturnFirstTokenDecoder(): ChainReturnFirstTokenDecoder
    {
        return new ChainReturnFirstTokenDecoder([new BasicAuthDecoder(), new ApiKeyAsBasicAuthUsernameDecoder()]);
    }
}
