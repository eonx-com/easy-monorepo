<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Stub\Entity;

use EonX\EasyEncryption\Encryptable\Attribute\EncryptableField;

/**
 * Two properties of one class stored under the same encrypted data key
 */
final class EncryptableSameFieldNameInClassEntityStub
{
    #[EncryptableField(fieldName: 'email')]
    private ?string $billingEmail = null;

    #[EncryptableField]
    private ?string $email = null;

    public function getBillingEmail(): ?string
    {
        return $this->billingEmail;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setBillingEmail(?string $billingEmail): void
    {
        $this->billingEmail = $billingEmail;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }
}
