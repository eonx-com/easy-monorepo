<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptable\Normaliser;

use EonX\EasyEncryption\Encryptable\Enum\HashNormalisation;

final class HashNormaliser implements HashNormaliserInterface
{
    public function normalise(string $value, HashNormalisation $normalisation): string
    {
        return match ($normalisation) {
            HashNormalisation::Lowercase => \mb_strtolower($value),
            HashNormalisation::Trim => \trim($value),
        };
    }
}
