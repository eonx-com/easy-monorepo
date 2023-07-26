<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Stubs\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'items')]
class Item
{
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    public int $id;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    public ?string $title = null;
}
