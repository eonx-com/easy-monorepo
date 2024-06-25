<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Fixture\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Tag
{
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'tags')]
    protected Product $product;

    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 128)]
    private string $name;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getProduct(): Product
    {
        return $this->product;
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

    public function setProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }
}
