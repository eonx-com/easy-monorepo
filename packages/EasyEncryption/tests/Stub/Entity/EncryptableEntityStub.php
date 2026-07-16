<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Stub\Entity;

use EonX\EasyEncryption\Encryptable\Attribute\EncryptableField;
use EonX\EasyEncryption\Encryptable\Encryptable\EncryptableInterface;
use EonX\EasyEncryption\Encryptable\Encryptable\EncryptableTrait;
use EonX\EasyEncryption\Encryptable\Enum\HashNormalization;

final class EncryptableEntityStub implements EncryptableInterface
{
    use EncryptableTrait;

    #[EncryptableField(hashNormalizations: [])]
    private ?string $caseSensitiveCode;

    #[EncryptableField]
    private ?string $email;

    #[EncryptableField(hashNormalizations: [HashNormalization::Lowercase, HashNormalization::Trim])]
    private ?string $username;

    public function __construct(
        ?string $email = null,
        ?string $username = null,
        ?string $caseSensitiveCode = null,
    ) {
        $this->email = $email;
        $this->username = $username;
        $this->caseSensitiveCode = $caseSensitiveCode;
    }

    public function getCaseSensitiveCode(): ?string
    {
        return $this->caseSensitiveCode;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getEncryptedData(): string
    {
        return $this->encryptedData;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }
}
