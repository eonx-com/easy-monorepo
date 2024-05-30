<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixtures\App\Case\EasyErrorHandler\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use EonX\EasyApiPlatform\Tests\Fixtures\App\Case\EasyErrorHandler\StateProvider\PrintingHouseStateProvider;

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
