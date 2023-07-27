<?php

declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixtures\App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource]
#[ORM\Entity]
class FourthLevel
{
    #[ORM\ManyToOne(targetEntity: ThirdLevel::class, cascade: ['persist'])]
    private ThirdLevel $badThirdLevel;

    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private int $id;

    #[Groups(['barcelona', 'chicago'])]
    #[ORM\Column(type: Types::INTEGER)]
    private int $level = 4;

    public function getBadThirdLevel(): ThirdLevel
    {
        return $this->badThirdLevel;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setBadThirdLevel(ThirdLevel $badThirdLevel): void
    {
        $this->badThirdLevel = $badThirdLevel;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }
}
