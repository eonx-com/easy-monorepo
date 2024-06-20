<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Fixture\App\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use EonX\EasyErrorHandler\Tests\Fixture\App\StateProvider\PrintingHouseStateProvider;

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
