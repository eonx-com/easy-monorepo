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
    public int $id;

    #[ORM\ManyToOne(targetEntity: Item::class)]
    public Item $item;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    public ?string $title = null;
}
