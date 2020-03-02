<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Stubs;

use EonX\EasyAsync\Bridge\WithProcessJobLogTrait;
use EonX\EasyAsync\Interfaces\WithProcessJobLogDataInterface;

final class WithProcessJobLogTraitStub
{
    use WithProcessJobLogTrait;

    /**
     * Process given func with given withData.
     *
     * @param \EonX\EasyAsync\Interfaces\WithProcessJobLogDataInterface $withData
     * @param callable $func
     *
     * @return void
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToGenerateDateTimeException
     * @throws \EonX\EasyAsync\Exceptions\UnableToPersistJobLogException
     */
    public function process(WithProcessJobLogDataInterface $withData, callable $func): void
    {
        $this->processWithJobLog($withData, $func);
    }
}
