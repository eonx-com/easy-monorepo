<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\DataTransferObject\DummyA;
use EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\DataTransferObject\DummyB;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: 'error-handler-dummies',
        ),
    ],
    openapi: false,
)]
final class Dummy
{
    public DummyA $dummyA;

    public DummyB $dummyB;
}
