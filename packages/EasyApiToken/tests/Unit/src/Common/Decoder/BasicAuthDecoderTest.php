<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Unit\Common\Decoder;

use EonX\EasyApiToken\Common\Decoder\BasicAuthDecoder;
use EonX\EasyApiToken\Common\ValueObject\BasicAuth;
use EonX\EasyApiToken\Tests\Unit\AbstractUnitTestCase;

final class BasicAuthDecoderTest extends AbstractUnitTestCase
{
    public function testBasicAuthNullIfAuthorizationHeaderNotSet(): void
    {
        self::assertNull(new BasicAuthDecoder()->decode($this->createRequest()));
    }

    public function testBasicAuthNullIfDoesntStartWithBasic(): void
    {
        self::assertNull(new BasicAuthDecoder()->decode($this->createRequest([
            'HTTP_AUTHORIZATION' => 'SomethingElse',
        ])));
    }

    public function testBasicAuthNullIfNoUsernameOrPasswordProvided(): void
    {
        $tests = ['', ':', ' : ', 'username', 'username:', ':password'];

        foreach ($tests as $test) {
            self::assertNull(new BasicAuthDecoder()->decode($this->createRequest([
                'HTTP_AUTHORIZATION' => 'Basic ' . \base64_encode($test),
            ])));
        }
    }

    public function testBasicAuthReturnEasyApiTokenSuccessfully(): void
    {
        // Value in header => [expectedUsername, expectedPassword]
        $tests = [
            'username:password' => ['username', 'password'],
            'username : password ' => ['username', 'password'],
            'username:Sp3c|@l_cH\\aracters' => ['username', 'Sp3c|@l_cH\\aracters'],
        ];

        foreach ($tests as $test => $expected) {
            /** @var \EonX\EasyApiToken\Common\ValueObject\BasicAuth $token */
            $token = new BasicAuthDecoder()
                ->decode($this->createRequest([
                    'HTTP_AUTHORIZATION' => \sprintf('Basic %s', \base64_encode($test)),
                ]));

            self::assertInstanceOf(BasicAuth::class, $token);
            self::assertEquals($expected[0], $token->getPayload()['username']);
            self::assertEquals($expected[1], $token->getPayload()['password']);
        }
    }
}
