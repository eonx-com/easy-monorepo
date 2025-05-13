<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\OpenApi\ApiResource;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    uriTemplate: 'open-api-categories',
)]
final class Category
{
    public string $name;
}
