<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Stub\Entity;

use EonX\EasyEncryption\Encryptable\Attribute\EncryptableField;

/**
 * Declares its own private "secretCode", as the parent does: two values for one encrypted data key
 */
final class EncryptableDuplicateFieldEntityStub extends AbstractEncryptableBaseEntityStub
{
    #[EncryptableField(fieldName: 'secret_code')]
    private ?string $secretCode;

    public function __construct(?string $secretCode = null)
    {
        parent::__construct($secretCode);

        $this->secretCode = $secretCode;
    }

    public function getOwnSecretCode(): ?string
    {
        return $this->secretCode;
    }

    public function setOwnSecretCode(?string $secretCode): void
    {
        $this->secretCode = $secretCode;
    }
}
