<?php

declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixtures\App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource]
#[ORM\Entity]
class ThirdLevel
{
    #[ORM\ManyToOne(targetEntity: FourthLevel::class, cascade: ['persist'])]
    public ?FourthLevel $badFourthLevel;

    #[ORM\ManyToOne(targetEntity: FourthLevel::class, cascade: ['persist'])]
    #[Groups(['barcelona', 'chicago', 'friends'])]
    public ?FourthLevel $fourthLevel;

    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['barcelona', 'chicago'])]
    private int $level = 3;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $test = true;

    public function getFourthLevel(): ?FourthLevel
    {
        return $this->fourthLevel;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function isTest(): bool
    {
        return $this->test;
    }

    public function setFourthLevel(?FourthLevel $fourthLevel = null): void
    {
        $this->fourthLevel = $fourthLevel;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function setTest(bool $test): void
    {
        $this->test = $test;
    }
}
