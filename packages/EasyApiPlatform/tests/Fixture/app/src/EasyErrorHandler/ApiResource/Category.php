<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\DataTransferObject\CategoryInputDto;
use EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\DataTransferObject\CategoryInputDtoWithConstructor;

#[ApiResource(
    operations: [
        new Post(),
        new Post(
            uriTemplate: 'categories-dto',
            input: CategoryInputDto::class
        ),
        new Post(
            uriTemplate: 'categories-dto-with-constructor',
            input: CategoryInputDtoWithConstructor::class,
        ),
    ]
)]
final class Category
{
    public string $name;

    public int $rank;
}
