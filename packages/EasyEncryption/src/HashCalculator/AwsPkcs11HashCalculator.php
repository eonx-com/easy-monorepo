<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\HashCalculator;

use EonX\EasyEncryption\Interfaces\AwsPkcs11EncryptorInterface;

final class AwsPkcs11HashCalculator implements HashCalculatorInterface
{
    private const DEFAULT_ENCODING = 'UTF-8';

    public function __construct(private AwsPkcs11EncryptorInterface $encryptor, private string $signKeyName)
    {
    }

    public function calculate(string $value): string
    {
        return \mb_convert_encoding($this->encryptor->sign($value, $this->signKeyName), self::DEFAULT_ENCODING);
    }
}
