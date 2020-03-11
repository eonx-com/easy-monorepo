<?php
declare(strict_types=1);

namespace EonX\EasyCore\Tests\Stubs;

use EonX\EasyCore\Lock\WithLockDataInterface;
use EonX\EasyCore\Lock\WithLockDataTrait;

final class WithLockDataStub implements WithLockDataInterface
{
    use WithLockDataTrait;
}
