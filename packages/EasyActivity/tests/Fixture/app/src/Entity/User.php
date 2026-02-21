<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Fixture\App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EonX\EasyEncryption\Encryptable\Attribute\EncryptableField;

#[ORM\Entity]
class User extends BaseUser
{
    #[EncryptableField]
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $creditCard;

    #[ORM\Column(type: Types::GUID)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    private string $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $password;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $username;

    public function getCreditCard(): string
    {
        return $this->creditCard;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setCreditCard(string $creditCard): self
    {
        $this->creditCard = $creditCard;

        return $this;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }
}
