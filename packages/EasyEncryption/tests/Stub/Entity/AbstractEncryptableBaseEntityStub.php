<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Stub\Entity;

use EonX\EasyEncryption\Encryptable\Attribute\EncryptableField;

/**
 * Base class with a private encryptable field and without the trait, see EncryptableChildEntityStub
 */
abstract class AbstractEncryptableBaseEntityStub
{
    #[EncryptableField(fieldName: 'secret_code')]
    private ?string $secretCode;

    public function __construct(?string $secretCode = null)
    {
        $this->secretCode = $secretCode;
    }

    public function getSecretCode(): ?string
    {
        return $this->secretCode;
    }

    public function setSecretCode(?string $secretCode): void
    {
        $this->secretCode = $secretCode;
    }
}
