<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Fixtures;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Offer
{
    /**
     * @var \Doctrine\Common\Collections\Collection<string|int, \EonX\EasyDoctrine\Tests\Fixtures\Product>
     */
    #[ORM\ManyToMany(targetEntity: Product::class, mappedBy: 'offers')]
    protected Collection $products;

    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 128)]
    private string $name;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function addProduct(Product $product): self
    {
        if ($this->products->contains($product) === false) {
            $this->products->add($product);
        }

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection<string|int, \EonX\EasyDoctrine\Tests\Fixtures\Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function removeProduct(Product $product): void
    {
        $this->products->removeElement($product);
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
