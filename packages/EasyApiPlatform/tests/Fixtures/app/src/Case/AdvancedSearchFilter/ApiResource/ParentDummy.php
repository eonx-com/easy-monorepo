<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixtures\App\Case\AdvancedSearchFilter\ApiResource;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[Orm\MappedSuperclass]
abstract class ParentDummy
{
    #[Groups(['friends'])]
    #[Orm\Column(type: Types::INTEGER, nullable: true)]
    private ?int $age = null;

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age = null): void
    {
        $this->age = $age;
    }
}
