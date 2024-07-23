<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Unit\Common\Decoder;

use EonX\EasyApiToken\Common\Decoder\ApiKeyDecoder;
use EonX\EasyApiToken\Common\Decoder\BasicAuthDecoder;
use EonX\EasyApiToken\Common\Decoder\ChainDecoder;
use EonX\EasyApiToken\Common\ValueObject\ApiKeyInterface;
use EonX\EasyApiToken\Common\ValueObject\BasicAuthInterface;
use EonX\EasyApiToken\Tests\Unit\AbstractUnitTestCase;

final class ChainReturnFirstTokenDecoderTest extends AbstractUnitTestCase
{
    public function testChainFirstApiKeyTokenSuccessfully(): void
    {
        /** @var \EonX\EasyApiToken\Common\ValueObject\ApiKeyInterface $token */
        $token = $this->createChainReturnFirstTokenDecoder()
            ->decode($this->createRequest([
                'HTTP_AUTHORIZATION' => 'Basic ' . \base64_encode('api-key: '),
            ]));

        self::assertInstanceOf(ApiKeyInterface::class, $token);
        self::assertEquals('api-key', $token->getPayload()['api_key']);
    }

    public function testChainFirstBasicAuthTokenSuccessfully(): void
    {
        /** @var \EonX\EasyApiToken\Common\ValueObject\BasicAuthInterface $token */
        $token = $this->createChainReturnFirstTokenDecoder()
            ->decode($this->createRequest([
                'HTTP_AUTHORIZATION' => 'Basic ' . \base64_encode('username:password'),
            ]));

        self::assertInstanceOf(BasicAuthInterface::class, $token);
        self::assertEquals('username', $token->getPayload()['username']);
        self::assertEquals('password', $token->getPayload()['password']);
    }

    public function testChainFirstNullIfNoDecoderCouldDecoderToken(): void
    {
        self::assertNull($this->createChainReturnFirstTokenDecoder()->decode($this->createRequest()));
    }

    private function createChainReturnFirstTokenDecoder(): ChainDecoder
    {
        return new ChainDecoder([new BasicAuthDecoder(), new ApiKeyDecoder()]);
    }
}
