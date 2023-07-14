<?php

declare(strict_types=1);

namespace EonX\EasyEncryption\Interfaces;

use Stringable;

interface DecryptedStringInterface extends Stringable
{
    public function getKeyName(): string;

    public function getRawDecryptedString(): string;
}
