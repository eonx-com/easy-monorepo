<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Stub\Entity;

use EonX\EasyEncryption\Encryptable\Attribute\EncryptableField;
use EonX\EasyEncryption\Encryptable\Encryptable\EncryptableInterface;
use EonX\EasyEncryption\Encryptable\Encryptable\EncryptableTrait;

final class EncryptableChildEntityStub extends AbstractEncryptableBaseEntityStub implements EncryptableInterface
{
    use EncryptableTrait;

    #[EncryptableField]
    private ?string $email;

    public function __construct(?string $email = null, ?string $secretCode = null)
    {
        parent::__construct($secretCode);

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
