<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\AwsCloudHsm\HashCalculator;

use EonX\EasyEncryption\AwsCloudHsm\Encryptor\AwsCloudHsmEncryptorInterface;
use EonX\EasyEncryption\Encryptable\HashCalculator\HashCalculatorInterface;

final readonly class AwsCloudHsmHashCalculator implements HashCalculatorInterface
{
    private const DEFAULT_ENCODING = 'UTF-8';

    public function __construct(
        private AwsCloudHsmEncryptorInterface $encryptor,
        private string $signKeyName,
    ) {
    }

    public function calculate(string $value): string
    {
        return \mb_convert_encoding($this->encryptor->sign($value, $this->signKeyName), self::DEFAULT_ENCODING);
    }
}
