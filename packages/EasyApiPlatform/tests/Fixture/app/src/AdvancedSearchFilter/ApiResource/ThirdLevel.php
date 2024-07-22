<?php
declare(strict_types=1);

<<<<<<<< HEAD:packages/EasyApiPlatform/tests/Fixtures/app/src/Case/AdvancedSearchFilter/ApiResource/ThirdLevel.php
namespace EonX\EasyApiPlatform\Tests\Fixtures\App\Case\AdvancedSearchFilter\ApiResource;
========
namespace EonX\EasyApiPlatform\Tests\Fixture\App\AdvancedSearchFilter\ApiResource;
>>>>>>>> refs/heads/6.x:packages/EasyApiPlatform/tests/Fixture/app/src/AdvancedSearchFilter/ApiResource/ThirdLevel.php

use ApiPlatform\Metadata\ApiResource;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource]
#[ORM\Entity]
class ThirdLevel
{
    #[ORM\ManyToOne(targetEntity: FourthLevel::class, cascade: ['persist'])]
    private ?FourthLevel $badFourthLevel = null;

    #[Groups(['barcelona', 'chicago', 'friends'])]
    #[ORM\ManyToOne(targetEntity: FourthLevel::class, cascade: ['persist'])]
    private ?FourthLevel $fourthLevel = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Id]
    private int $id;

    #[Groups(['barcelona', 'chicago'])]
    #[ORM\Column(type: Types::INTEGER)]
    private int $level = 3;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $test = true;

    public function getBadFourthLevel(): ?FourthLevel
    {
        return $this->badFourthLevel;
    }

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

    public function setBadFourthLevel(?FourthLevel $badFourthLevel = null): void
    {
        $this->badFourthLevel = $badFourthLevel;
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
