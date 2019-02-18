<?php
declare(strict_types=1);

namespace StepTheFkUp\MockBuilder\Returns;

use Mockery\ExpectationInterface;
use StepTheFkUp\MockBuilder\Interfaces\ReturnInterface;

final class ReturnSelf implements ReturnInterface
{
    /**
     * Apply return to expectation.
     *
     * @param \Mockery\ExpectationInterface|\Mockery\Expectation $expectation
     *
     * @return void
     */
    public function doReturn(ExpectationInterface $expectation): void
    {
        $expectation->andReturnSelf();
    }
}
