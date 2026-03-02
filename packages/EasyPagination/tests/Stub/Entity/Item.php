<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Stub\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EonX\EasyPagination\Tests\Stub\Enum\Status;

#[ORM\Entity]
#[ORM\Table(name: 'items')]
class Item
{
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, enumType: Status::class)]
    private ?Status $status = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $title = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setStatus(?Status $status): void
    {
        $this->status = $status;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }
}
