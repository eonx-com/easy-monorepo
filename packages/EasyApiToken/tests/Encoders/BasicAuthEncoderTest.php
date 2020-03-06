<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Encoders;

use EonX\EasyApiToken\Encoders\BasicAuthEncoder;
use EonX\EasyApiToken\Exceptions\InvalidArgumentException;
use EonX\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException;
use EonX\EasyApiToken\Tests\AbstractTestCase;
use EonX\EasyApiToken\Tokens\ApiKeyEasyApiToken;
use EonX\EasyApiToken\Tokens\BasicAuthEasyApiToken;

final class BasicAuthEncoderTest extends AbstractTestCase
{
    public function testBasicAuthEncodeSuccessfully(): void
    {
        $tests = [
            'username' => 'password',
            'username ' => ' password ',
            'username  ' => 'Sp3c|@l_cH\\aracters',
        ];

        foreach ($tests as $username => $password) {
            $token = (new BasicAuthEncoder())->encode(new BasicAuthEasyApiToken($username, $password, 'original'));

            self::assertEquals(\base64_encode(\sprintf('%s:%s', $username, $password)), $token);
        }
    }

    public function testInvalidTokenException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new BasicAuthEncoder())->encode(new ApiKeyEasyApiToken(''));
    }

    public function testUnableToEncodePasswordEmptyException(): void
    {
        $this->expectException(UnableToEncodeEasyApiTokenException::class);

        (new BasicAuthEncoder())->encode(new BasicAuthEasyApiToken('username', '', ''));
    }

    public function testUnableToEncodeUsernameEmptyException(): void
    {
        $this->expectException(UnableToEncodeEasyApiTokenException::class);

        (new BasicAuthEncoder())->encode(new BasicAuthEasyApiToken('', 'password', ''));
    }
}
