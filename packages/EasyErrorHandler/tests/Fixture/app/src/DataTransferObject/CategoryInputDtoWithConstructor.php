<?php
declare(strict_types=1);

<<<<<<<< HEAD:packages/EasyApiPlatform/tests/Fixtures/app/src/Case/EasyErrorHandler/DataTransferObject/CategoryInputDtoWithConstructor.php
namespace EonX\EasyApiPlatform\Tests\Fixtures\App\Case\EasyErrorHandler\DataTransferObject;
========
namespace EonX\EasyErrorHandler\Tests\Fixture\App\DataTransferObject;
>>>>>>>> refs/heads/6.x:packages/EasyErrorHandler/tests/Fixture/app/src/DataTransferObject/CategoryInputDtoWithConstructor.php

final class CategoryInputDtoWithConstructor
{
    public function __construct(
        public string $name,
        public int $rank,
    ) {
    }
}
