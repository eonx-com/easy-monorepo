<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptable\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class EncryptableField
{
    public function __construct(
        private ?string $fieldName = null,
    ) {
    }

    public function getFieldName(): ?string
    {
        return $this->fieldName;
    }
}
