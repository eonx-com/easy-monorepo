<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Stubs;

use EonX\EasyAsync\Bridge\WithProcessJobLogTrait;
use EonX\EasyAsync\Interfaces\WithProcessJobLogDataInterface;

final class WithProcessJobLogTraitStub
{
    use WithProcessJobLogTrait;

    public function process(WithProcessJobLogDataInterface $withData, callable $func): void
    {
        $this->processWithJobLog($withData, $func);
    }
}
