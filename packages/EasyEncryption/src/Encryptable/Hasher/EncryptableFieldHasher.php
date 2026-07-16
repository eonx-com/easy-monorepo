<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptable\Hasher;

use EonX\EasyEncryption\Encryptable\Enum\HashNormalization;
use EonX\EasyEncryption\Encryptable\HashCalculator\HashCalculatorInterface;
use EonX\EasyEncryption\Encryptable\Metadata\EncryptableMetadataInterface;
use EonX\EasyEncryption\Encryptable\Normalizer\HashNormalizerInterface;

final readonly class EncryptableFieldHasher implements EncryptableFieldHasherInterface
{
    /**
     * @var \EonX\EasyEncryption\Encryptable\Enum\HashNormalization[]
     */
    private array $defaultHashNormalizations;

    /**
     * @param string[] $defaultHashNormalizations
     */
    public function __construct(
        private HashCalculatorInterface $hashCalculator,
        private EncryptableMetadataInterface $metadata,
        private HashNormalizerInterface $hashNormalizer,
        array $defaultHashNormalizations = [],
    ) {
        $this->defaultHashNormalizations = \array_map(
            static fn (string $normalization): HashNormalization => HashNormalization::from($normalization),
            $defaultHashNormalizations
        );
    }

    public function hashForField(string $entityClass, string $propertyName, string $value): string
    {
        $normalizations = $this->metadata->getHashNormalizationsForField($entityClass, $propertyName)
            ?? $this->defaultHashNormalizations;

        foreach ($normalizations as $normalization) {
            $value = $this->hashNormalizer->normalize($value, $normalization);
        }

        return $this->hashCalculator->calculate($value);
    }
}
