<?php
declare(strict_types=1);

<<<<<<<< HEAD:packages/EasyApiPlatform/tests/Fixtures/app/src/Case/EasyErrorHandler/DataTransferObject/Author.php
namespace EonX\EasyApiPlatform\Tests\Fixtures\App\Case\EasyErrorHandler\DataTransferObject;
========
namespace EonX\EasyErrorHandler\Tests\Fixture\App\DataTransferObject;
>>>>>>>> refs/heads/6.x:packages/EasyErrorHandler/tests/Fixture/app/src/DataTransferObject/Author.php

final readonly class Author
{
    public function __construct(
        private string $name,
        private int $age,
    ) {
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
