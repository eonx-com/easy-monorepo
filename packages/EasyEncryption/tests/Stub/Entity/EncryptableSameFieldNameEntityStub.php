<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Stub\Entity;

use EonX\EasyEncryption\Encryptable\Attribute\EncryptableField;

/**
 * Its own property is stored under the same encrypted data key as the private property of the parent
 */
final class EncryptableSameFieldNameEntityStub extends AbstractEncryptableBaseEntityStub
{
    #[EncryptableField(fieldName: 'secret_code')]
    private ?string $code;

    public function __construct(?string $code = null, ?string $secretCode = null)
    {
        parent::__construct($secretCode);

        $this->code = $code;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }
}
