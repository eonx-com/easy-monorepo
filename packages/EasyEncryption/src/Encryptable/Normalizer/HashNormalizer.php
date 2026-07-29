<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptable\Normalizer;

use EonX\EasyEncryption\Encryptable\Enum\HashNormalization;

final class HashNormalizer implements HashNormalizerInterface
{
    public function normalize(string $value, HashNormalization $normalization): string
    {
        return match ($normalization) {
            HashNormalization::Lowercase => \mb_strtolower($value),
            HashNormalization::Trim => \trim($value),
        };
    }
}
