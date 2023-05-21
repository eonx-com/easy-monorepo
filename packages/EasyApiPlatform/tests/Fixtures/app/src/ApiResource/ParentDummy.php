<?php

declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixtures\App\ApiResource;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[Orm\MappedSuperclass]
class ParentDummy
{
    #[Orm\Column(type: Types::INTEGER, nullable: true)]
    #[Groups(['friends'])]
    private ?int $age;

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age = null): void
    {
        $this->age = $age;
    }
}
