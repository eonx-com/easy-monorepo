<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Doctrine\Fixtures;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Product
{
    /**
     * @ORM\ManyToOne(targetEntity="EonX\EasyCore\Tests\Doctrine\Fixtures\Category")
     *
     * @var \EonX\EasyCore\Tests\Doctrine\Fixtures\Category|null
     */
    private $category;

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

    /**
     * @ORM\Column(type="bigint")
     *
     * @var string
     */
    private $price;

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }
}
