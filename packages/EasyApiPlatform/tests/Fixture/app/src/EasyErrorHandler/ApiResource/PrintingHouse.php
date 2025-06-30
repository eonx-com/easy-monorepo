<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\Enum\Status;
use EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\StateProvider\PrintingHouseStateProvider;

#[ApiResource(
    openapi: false,
    provider: PrintingHouseStateProvider::class,
)]
final class PrintingHouse
{
    #[ApiProperty(identifier: true)]
    public int $id;

    public function __construct(
        private readonly string $name,
        private readonly Status $status,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }
}
