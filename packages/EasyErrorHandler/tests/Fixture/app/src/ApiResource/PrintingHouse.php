<?php
declare(strict_types=1);

<<<<<<<< HEAD:packages/EasyApiPlatform/tests/Fixtures/app/src/Case/EasyErrorHandler/ApiResource/PrintingHouse.php
namespace EonX\EasyApiPlatform\Tests\Fixtures\App\Case\EasyErrorHandler\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use EonX\EasyApiPlatform\Tests\Fixtures\App\Case\EasyErrorHandler\StateProvider\PrintingHouseStateProvider;
========
namespace EonX\EasyErrorHandler\Tests\Fixture\App\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use EonX\EasyErrorHandler\Tests\Fixture\App\StateProvider\PrintingHouseStateProvider;
>>>>>>>> refs/heads/6.x:packages/EasyErrorHandler/tests/Fixture/app/src/ApiResource/PrintingHouse.php

#[ApiResource(
    provider: PrintingHouseStateProvider::class
)]
final class PrintingHouse
{
    #[ApiProperty(identifier: true)]
    public int $id;

    public function __construct(
        private readonly string $name,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }
}
