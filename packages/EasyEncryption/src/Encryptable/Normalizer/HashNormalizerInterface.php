<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptable\Normalizer;

use EonX\EasyEncryption\Encryptable\Enum\HashNormalization;

interface HashNormalizerInterface
{
    public function normalize(string $value, HashNormalization $normalization): string;
}
