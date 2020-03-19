<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Stubs;

use EonX\EasyAsync\Bridge\WithProcessJobLogDataTrait;
use EonX\EasyAsync\Interfaces\WithProcessJobLogDataInterface;

final class WithProcessJobLogDataStub implements WithProcessJobLogDataInterface
{
    use WithProcessJobLogDataTrait;
}
