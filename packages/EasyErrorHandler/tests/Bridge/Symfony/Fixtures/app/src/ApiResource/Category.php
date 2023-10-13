<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Symfony\Fixtures\App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use EonX\EasyErrorHandler\Tests\Bridge\Symfony\Fixtures\App\DataTransferObject\CategoryInputDto;
use EonX\EasyErrorHandler\Tests\Bridge\Symfony\Fixtures\App\DataTransferObject\CategoryInputDtoWithConstructor;

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
