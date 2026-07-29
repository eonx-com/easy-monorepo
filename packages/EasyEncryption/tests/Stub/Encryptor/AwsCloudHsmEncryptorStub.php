<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Stub\Encryptor;

use EonX\EasyEncryption\AwsCloudHsm\Encryptor\AwsCloudHsmEncryptorInterface;
use EonX\EasyEncryption\Common\ValueObject\DecryptedString;
use RuntimeException;

final class AwsCloudHsmEncryptorStub implements AwsCloudHsmEncryptorInterface
{
    public function decrypt(string $text): DecryptedString
    {
        throw new RuntimeException('Not implemented in stub.');
    }

    public function decryptRaw(string $text, array|string|null $key = null): string
    {
        throw new RuntimeException('Not implemented in stub.');
    }

    public function encrypt(string $text, ?string $keyName = null): string
    {
        throw new RuntimeException('Not implemented in stub.');
    }

    public function encryptRaw(string $text, array|string|null $key = null): string
    {
        throw new RuntimeException('Not implemented in stub.');
    }

    public function init(): void {}

    public function reset(): void {}

    public function sign(string $text, ?string $keyName = null): string
    {
        return \hash('sha256', ($keyName ?? '') . ':' . $text);
    }
}
