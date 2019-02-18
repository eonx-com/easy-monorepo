<?php
declare(strict_types=1);

namespace StepTheFkUp\MockBuilder\Interfaces;

use Closure;
use Mockery\MockInterface;

interface MockBuilderInterface
{
    /**
     * Add closure config.
     *
     * @param \Closure $closure
     *
     * @return static
     */
    public function addConfiguration(Closure $closure);

    /**
     * Add expectation that mock should return expected value.
     *
     * @param mixed $return
     *
     * @return static
     */
    public function andReturn($return);

    /**
     * Add expectation that mock should return expected value.
     *
     * @return static
     */
    public function andReturnSelf();

    /**
     * Add expectation that mock should throw exception.
     *
     * @param string $exception
     *
     * @return static
     */
    public function andThrow(string $exception);

    /**
     * Build mock object based on configurations. This method is called last after all other configurations.
     *
     * @return \Mockery\MockInterface
     */
    public function build(): MockInterface;

    /**
     * Add expectation that mock should be called a number of times.
     *
     * @param int $count
     *
     * @return static
     */
    public function times(int $count);
}
