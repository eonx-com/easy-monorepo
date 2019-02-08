<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tests\Decoders;

use StepTheFkUp\ApiToken\Decoders\JwtTokenInQueryDecoder;
use StepTheFkUp\ApiToken\Tests\AbstractJwtTokenTestCase;
use StepTheFkUp\ApiToken\Tokens\JwtApiToken;

final class JwtTokenInQueryDecoderTest extends AbstractJwtTokenTestCase
{
    /**
     * JwtTokenDecoder should decode token successfully for each algorithms.
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidApiTokenFromRequestException
     */
    public function testJwtTokenDecodeSuccessfully(): void
    {
        foreach (static::$algos as $algo) {
            $key = static::$key;

            if ($this->isAlgoRs($algo)) {
                $key = $this->getOpenSslPublicKey();
            }

            $jwtApiTokenFactory = $this->createJwtApiTokenFactory(null, $key, null, [$algo]);
            $decoder = new JwtTokenInQueryDecoder($jwtApiTokenFactory, 'param');
            $request = $this->createServerRequest(null, [
                'param' => $this->createToken($algo)
            ]);

            $token = $decoder->decode($request);

            $payload = $token->getPayload();

            self::assertInstanceOf(JwtApiToken::class, $token);

            foreach (static::$tokenPayload as $key => $value) {
                self::assertArrayHasKey($key, $payload);
                self::assertEquals($value, $payload[$key]);
            }
        }
    }

    /**
     * JwtTokenInQueryDecoder should return null if query param not set on request.
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidApiTokenFromRequestException
     */
    public function testNullWhenQueryParamNotSet(): void
    {
        $decoder = new JwtTokenInQueryDecoder($this->createJwtApiTokenFactory(), 'param');

        self::assertNull($decoder->decode($this->createServerRequest()));
    }
}
