<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Fixtures;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Product
{
    #[ORM\ManyToOne(targetEntity: Category::class)]
    private ?Category $category = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 128)]
    private string $name;

    #[ORM\Column(type: PriceType::NAME)]
    private Price $price;

    /**
     * @var \Doctrine\Common\Collections\Collection<string|int, \EonX\EasyDoctrine\Tests\Fixtures\Tag>
     */
    #[ORM\OneToMany(
        mappedBy: 'product',
        targetEntity: Tag::class,
        cascade: ['persist'],
        orphanRemoval: true
    )]
    private Collection $tags;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    public function addTag(Tag $tag): self
    {
        if ($this->tags->contains($tag) === false) {
            $this->tags->add($tag);
            $tag->setProduct($this);
        }

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): Price
    {
        return $this->price;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection<string|int, \EonX\EasyDoctrine\Tests\Fixtures\Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setPrice(Price $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @param iterable<\EonX\EasyDoctrine\Tests\Fixtures\Tag> $tags
     */
    public function setTags(iterable $tags): self
    {
        $this->tags->clear();

        foreach ($tags as $tag) {
            $this->addTag($tag);
        }

        return $this;
    }
}
