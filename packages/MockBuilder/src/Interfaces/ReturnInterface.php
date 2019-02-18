<?php
declare(strict_types=1);

namespace StepTheFkUp\MockBuilder\Interfaces;

use Mockery\ExpectationInterface;

interface ReturnInterface
{
    /**
     * Apply return to expectation.
     *
     * @param \Mockery\ExpectationInterface|\Mockery\Expectation $expectation
     *
     * @return void
     */
    public function doReturn(ExpectationInterface $expectation): void;
}
