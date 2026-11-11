<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Stub\Entity;

use EonX\EasyEncryption\Encryptable\Attribute\EncryptableField;
use EonX\EasyEncryption\Encryptable\Encryptable\EncryptableInterface;
use EonX\EasyEncryption\Encryptable\Encryptable\EncryptableTrait;
use EonX\EasyEncryption\Encryptable\Enum\HashNormalization;

/**
 * Entity with a private encryptable field that has a subclass, see EncryptableProxyEntityStub
 */
abstract class AbstractEncryptableParentEntityStub implements EncryptableInterface
{
    use EncryptableTrait;

    #[EncryptableField(hashNormalizations: [HashNormalization::Lowercase])]
    private ?string $email;

    public function __construct(?string $email = null)
    {
        $this->email = $email;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getEncryptedData(): string
    {
        return $this->encryptedData;
    }
}
