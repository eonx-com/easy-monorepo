<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\AwsCloudHsm\Encryptor;

use EonX\EasyEncryption\Common\Encryptor\EncryptorInterface;

interface AwsCloudHsmEncryptorInterface extends EncryptorInterface
{
    public function reset(): void;

    public function sign(string $text, ?string $keyName = null): string;
}
