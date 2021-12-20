<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Fixtures;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Category
{
    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTimeInterface|null
     */
    private $activeTill;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=128)
     *
     * @var string
     */
    private $name;

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
