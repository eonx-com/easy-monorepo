<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Decoders;

use EonX\EasyApiToken\Decoders\BasicAuthDecoder;
use EonX\EasyApiToken\Interfaces\Tokens\BasicAuthInterface;
use EonX\EasyApiToken\Tests\AbstractTestCase;

final class BasicAuthDecoderTest extends AbstractTestCase
{
    public function testBasicAuthNullIfAuthorizationHeaderNotSet(): void
    {
        self::assertNull((new BasicAuthDecoder())->decode($this->createRequest()));
    }

    public function testBasicAuthNullIfDoesntStartWithBasic(): void
    {
        self::assertNull((new BasicAuthDecoder())->decode($this->createRequest([
            'HTTP_AUTHORIZATION' => 'SomethingElse',
        ])));
    }

    public function testBasicAuthNullIfNoUsernameOrPasswordProvided(): void
    {
        $tests = ['', ':', ' : ', 'username', 'username:', ':password'];

        foreach ($tests as $test) {
            self::assertNull((new BasicAuthDecoder())->decode($this->createRequest([
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
            /** @var \EonX\EasyApiToken\Interfaces\Tokens\BasicAuthInterface $token */
            $token = (new BasicAuthDecoder())->decode($this->createRequest([
                'HTTP_AUTHORIZATION' => \sprintf('Basic %s', \base64_encode($test)),
            ]));

            self::assertInstanceOf(BasicAuthInterface::class, $token);
            self::assertEquals($expected[0], $token->getPayload()['username']);
            self::assertEquals($expected[1], $token->getPayload()['password']);
        }
    }
}
