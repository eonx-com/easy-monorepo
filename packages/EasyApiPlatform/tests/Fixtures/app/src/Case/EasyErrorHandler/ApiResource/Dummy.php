<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixtures\App\Case\EasyErrorHandler\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use EonX\EasyApiPlatform\Tests\Fixtures\App\Case\EasyErrorHandler\DataTransferObject\DummyA;
use EonX\EasyApiPlatform\Tests\Fixtures\App\Case\EasyErrorHandler\DataTransferObject\DummyB;

#[ApiResource]
final class Dummy
{
    public DummyA $dummyA;

    public DummyB $dummyB;
}
