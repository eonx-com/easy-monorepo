<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Fixture\App\Entity;

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EonX\EasyDoctrine\Common\Entity\TimestampableInterface;
use EonX\EasyDoctrine\Common\Entity\TimestampableTrait;

#[ORM\Entity]
class Category implements TimestampableInterface
{
    use TimestampableTrait;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $activeTill = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 128)]
    private string $name;

    public function getActiveTill(): ?DateTimeInterface
    {
        return $this->activeTill;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setActiveTill(?DateTimeInterface $activeTill = null): self
    {
        $this->activeTill = $activeTill;

        return $this;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
