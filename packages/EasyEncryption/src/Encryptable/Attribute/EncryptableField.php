<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptable\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class EncryptableField
{
    /**
     * @param \EonX\EasyEncryption\Encryptable\Enum\HashNormalization[]|null $hashNormalizations
     */
    public function __construct(
        private ?string $fieldName = null,
        private ?array $hashNormalizations = null,
    ) {
    }

    public function getFieldName(): ?string
    {
        return $this->fieldName;
    }

    /**
     * @return \EonX\EasyEncryption\Encryptable\Enum\HashNormalization[]|null
     */
    public function getHashNormalizations(): ?array
    {
        return $this->hashNormalizations;
    }
}
