<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Fixture\App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EonX\EasyEncryption\Encryptable\Attribute\EncryptableField;

#[ORM\MappedSuperclass]
abstract class BaseUser
{
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $email;

    #[EncryptableField]
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $ssn;

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSsn(): string
    {
        return $this->ssn;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function setSsn(string $ssn): static
    {
        $this->ssn = $ssn;

        return $this;
    }
}
