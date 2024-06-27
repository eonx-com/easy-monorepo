<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Fixture\App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use EonX\EasyErrorHandler\Tests\Fixture\App\DataTransferObject\DummyA;
use EonX\EasyErrorHandler\Tests\Fixture\App\DataTransferObject\DummyB;

#[ApiResource]
final class Dummy
{
    public DummyA $dummyA;

    public DummyB $dummyB;
}
