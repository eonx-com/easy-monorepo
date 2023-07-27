<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Stubs\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'child_items')]
class ChildItem
{
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Item::class)]
    private Item $item;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $title = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setItem(Item $item): void
    {
        $this->item = $item;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }
}
