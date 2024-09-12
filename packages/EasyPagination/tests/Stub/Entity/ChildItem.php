<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Stub\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EonX\EasyPagination\Tests\Stub\Type\SqliteStringUuidType;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'child_items')]
class ChildItem
{
    #[ORM\Column(type: SqliteStringUuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Id]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: Item::class)]
    private Item $item;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $title = null;

    public function getId(): Uuid
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
