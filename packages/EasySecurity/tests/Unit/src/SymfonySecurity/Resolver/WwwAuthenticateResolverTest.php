<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\SymfonySecurity\Resolver;

use EonX\EasyApiToken\Common\Decoder\ApiKeyDecoder;
use EonX\EasyApiToken\Common\Decoder\BasicAuthDecoder;
use EonX\EasyApiToken\Common\Decoder\ChainDecoder;
use EonX\EasyApiToken\Common\Decoder\DecoderInterface;
use EonX\EasyApiToken\Common\Exception\InvalidDefaultDecoderException;
use EonX\EasyApiToken\Common\ValueObject\ApiKey;
use EonX\EasyApiToken\Common\ValueObject\BasicAuth;
use EonX\EasyApiToken\Common\ValueObject\HashedApiKey;
use EonX\EasyApiToken\Common\ValueObject\Jwt;
use EonX\EasySecurity\SymfonySecurity\Resolver\WwwAuthenticateResolver;
use EonX\EasySecurity\Tests\Stub\Decoder\TokenDecoderStub;
use EonX\EasySecurity\Tests\Stub\Factory\ApiTokenDecoderFactoryStub;
use EonX\EasySecurity\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

final class WwwAuthenticateResolverTest extends AbstractUnitTestCase
{
    /**
     * @see testResolveSucceeds
     */
    public static function provideResolveData(): iterable
    {
        yield 'JWT' => [
            'decoder' => new TokenDecoderStub(new Jwt([], 'jwt')),
            'authorization' => null,
            'expectedChallenge' => 'Bearer',
        ];

        yield 'API key' => [
            'decoder' => new TokenDecoderStub(new ApiKey('api-key')),
            'authorization' => null,
            'expectedChallenge' => 'Basic realm="api-key"',
        ];

        yield 'Hashed API key' => [
            'decoder' => new TokenDecoderStub(new HashedApiKey('id', 'secret', 'original')),
            'authorization' => null,
            'expectedChallenge' => 'Basic realm="api-key"',
        ];

        yield 'Basic auth' => [
            'decoder' => new TokenDecoderStub(new BasicAuth('username', 'password', 'original')),
            'authorization' => null,
            'expectedChallenge' => 'Basic realm="user"',
        ];

        yield 'No token' => [
            'decoder' => new TokenDecoderStub(),
            'authorization' => null,
            'expectedChallenge' => 'Default',
        ];

        yield 'Chain decoder with API key in request' => [
            'decoder' => new ChainDecoder([new BasicAuthDecoder(), new ApiKeyDecoder()]),
            'authorization' => 'Basic ' . \base64_encode('api-key:'),
            'expectedChallenge' => 'Basic realm="api-key"',
        ];

        yield 'Chain decoder with username and password in request' => [
            'decoder' => new ChainDecoder([new BasicAuthDecoder(), new ApiKeyDecoder()]),
            'authorization' => 'Basic ' . \base64_encode('username:password'),
            'expectedChallenge' => 'Basic realm="user"',
        ];

        yield 'Chain decoder without Authorization header' => [
            'decoder' => new ChainDecoder([new BasicAuthDecoder(), new ApiKeyDecoder()]),
            'authorization' => null,
            'expectedChallenge' => 'Default',
        ];

        yield 'Empty chain decoder' => [
            'decoder' => new ChainDecoder([]),
            'authorization' => 'Basic ' . \base64_encode('api-key:'),
            'expectedChallenge' => 'Default',
        ];

        yield 'Decoder factory throws exception' => [
            'decoder' => new InvalidDefaultDecoderException('No default decoder set'),
            'authorization' => null,
            'expectedChallenge' => 'Default',
        ];
    }

    #[DataProvider('provideResolveData')]
    public function testResolveSucceeds(
        DecoderInterface|Throwable $decoder,
        ?string $authorization,
        string $expectedChallenge,
    ): void {
        $request = new Request();
        if ($authorization !== null) {
            $request->headers->set('Authorization', $authorization);
        }
        $sut = new WwwAuthenticateResolver('Default', new ApiTokenDecoderFactoryStub($decoder));

        $result = $sut->resolve($request);

        self::assertSame($expectedChallenge, $result);
    }

    public function testResolveSucceedsWithoutDecoderFactory(): void
    {
        $sut = new WwwAuthenticateResolver('Default');

        $result = $sut->resolve(new Request());

        self::assertSame('Default', $result);
    }
}
