<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptable\Hasher;

use EonX\EasyEncryption\Encryptable\Enum\HashNormalisation;
use EonX\EasyEncryption\Encryptable\HashCalculator\HashCalculatorInterface;
use EonX\EasyEncryption\Encryptable\Metadata\EncryptableMetadataInterface;
use EonX\EasyEncryption\Encryptable\Normaliser\HashNormaliserInterface;

final class EncryptableFieldHasher implements EncryptableFieldHasherInterface
{
    /**
     * @var \EonX\EasyEncryption\Encryptable\Enum\HashNormalisation[]
     */
    private readonly array $defaultHashNormalisations;

    /**
     * @param string[] $defaultHashNormalisations
     */
    public function __construct(
        private readonly HashCalculatorInterface $hashCalculator,
        private readonly EncryptableMetadataInterface $metadata,
        private readonly HashNormaliserInterface $hashNormaliser,
        array $defaultHashNormalisations = [],
    ) {
        $this->defaultHashNormalisations = \array_map(
            static fn (string $normalisation): HashNormalisation => HashNormalisation::from($normalisation),
            $defaultHashNormalisations
        );
    }

    public function hashForField(string $entityClass, string $field, string $value): string
    {
        $normalisations = $this->metadata->getHashNormalisationsForField($entityClass, $field)
            ?? $this->defaultHashNormalisations;

        foreach ($normalisations as $normalisation) {
            $value = $this->hashNormaliser->normalise($value, $normalisation);
        }

        return $this->hashCalculator->calculate($value);
    }
}
