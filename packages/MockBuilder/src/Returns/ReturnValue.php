<?php
declare(strict_types=1);

namespace StepTheFkUp\MockBuilder\Returns;

use Mockery\ExpectationInterface;
use StepTheFkUp\MockBuilder\Interfaces\ReturnInterface;

final class ReturnValue implements ReturnInterface
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * ReturnValue constructor.
     *
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Apply return to expectation.
     *
     * @param \Mockery\ExpectationInterface|\Mockery\Expectation $expectation
     *
     * @return void
     */
    public function doReturn(ExpectationInterface $expectation): void
    {
        $expectation->andReturn($this->value);
    }
}
