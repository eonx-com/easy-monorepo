<?php
declare(strict_types=1);

<<<<<<<< HEAD:packages/EasyApiPlatform/tests/Fixtures/app/src/Case/EasyErrorHandler/ApiResource/Category.php
namespace EonX\EasyApiPlatform\Tests\Fixtures\App\Case\EasyErrorHandler\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use EonX\EasyApiPlatform\Tests\Fixtures\App\Case\EasyErrorHandler\DataTransferObject\CategoryInputDto;
use EonX\EasyApiPlatform\Tests\Fixtures\App\Case\EasyErrorHandler\DataTransferObject\CategoryInputDtoWithConstructor;
========
namespace EonX\EasyErrorHandler\Tests\Fixture\App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use EonX\EasyErrorHandler\Tests\Fixture\App\DataTransferObject\CategoryInputDto;
use EonX\EasyErrorHandler\Tests\Fixture\App\DataTransferObject\CategoryInputDtoWithConstructor;
>>>>>>>> refs/heads/6.x:packages/EasyErrorHandler/tests/Fixture/app/src/ApiResource/Category.php

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
