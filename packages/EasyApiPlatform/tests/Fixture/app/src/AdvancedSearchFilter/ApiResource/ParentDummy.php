<?php
declare(strict_types=1);

<<<<<<<< HEAD:packages/EasyApiPlatform/tests/Fixtures/app/src/Case/AdvancedSearchFilter/ApiResource/ParentDummy.php
namespace EonX\EasyApiPlatform\Tests\Fixtures\App\Case\AdvancedSearchFilter\ApiResource;
========
namespace EonX\EasyApiPlatform\Tests\Fixture\App\AdvancedSearchFilter\ApiResource;
>>>>>>>> refs/heads/6.x:packages/EasyApiPlatform/tests/Fixture/app/src/AdvancedSearchFilter/ApiResource/ParentDummy.php

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
