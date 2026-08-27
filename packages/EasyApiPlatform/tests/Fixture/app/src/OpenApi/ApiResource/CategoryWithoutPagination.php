<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\OpenApi\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;

#[ApiResource(
    uriTemplate: 'open-api-categories-without-pagination',
    operations: [new GetCollection()],
    paginationEnabled: false,
)]
final class CategoryWithoutPagination
{
    public string $name;
}
