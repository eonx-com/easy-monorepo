<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptable\Normaliser;

use EonX\EasyEncryption\Encryptable\Enum\HashNormalisation;

interface HashNormaliserInterface
{
    public function normalise(string $value, HashNormalisation $normalisation): string;
}
