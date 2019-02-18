<?php
declare(strict_types=1);

namespace StepTheFkUp\MockBuilder;

use BadMethodCallException;
use Closure;
use Mockery;
use Mockery\ExpectationInterface;
use Mockery\MockInterface;
use StepTheFkUp\MockBuilder\Interfaces\MockBuilderInterface;
use StepTheFkUp\MockBuilder\Interfaces\ReturnInterface;
use StepTheFkUp\MockBuilder\Returns\ReturnSelf;
use StepTheFkUp\MockBuilder\Returns\ReturnValue;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Suppress due to dependency
 */
abstract class AbstractMockBuilder implements MockBuilderInterface
{
    /**
     * @var \Closure[]
     */
    private $configurations = [];

    /**
     * @var string[]
     */
    private $exceptions = [];

    /**
     * @var null|object
     */
    private $finalInstance;

    /**
     * @var mixed[]
     */
    private $returns = [];

    /**
     * @var int[]
     */
    private $times = [];

    /**
     * AbstractMockBuilder constructor.
     *
     * @param null|object $finalInstance An instance of a class to mock if it's final for partial mocking.
     */
    public function __construct($finalInstance = null)
    {
        $this->finalInstance = $finalInstance;
    }

    /**
     * Call has{expectedMethods} magically.
     *
     * @param string $name
     * @param mixed $arguments
     *
     * @return static
     */
    public function __call(string $name, $arguments): self
    {
        if (\strpos($name, 'has') !== 0) {
            throw new BadMethodCallException(\sprintf(
                'Method expectation `%s` not allowed. Should be `has%s`.',
                $name,
                \ucfirst($name)
            ));
        }
        $method = \lcfirst(\substr($name, 3));

        if (\in_array($method, $this->getAvailableMethods(), true) === false) {
            throw new BadMethodCallException(\sprintf(
                'Undefined/Invalid expected method `%s` in class `%s`. Available methods: `%s`.',
                $method,
                $this->getClassToMock(),
                \implode('`, `', $this->getAvailableMethods())
            ));
        }

        $this->addConfiguration(
            function (
                MockInterface $mock,
                int $times = null,
                $return = null,
                string $exception = null
            ) use (
                $method,
                $arguments
            ): void {
                $expectation = $mock->shouldReceive($method)->times($times ?? 1)->with(...$arguments);
                $this->doReturn($expectation, $return);

                if (\count($arguments) === 1 && $arguments[0] instanceof Closure) {
                    $expectation->withArgs($arguments[0]);
                }

                if ($exception !== null) {
                    $expectation->andThrow($exception);
                }
            }
        );

        return $this;
    }

    /**
     * Add closure config.
     *
     * @param \Closure $closure
     *
     * @return static
     */
    public function addConfiguration(Closure $closure): self
    {
        $this->configurations[] = $closure;

        return $this;
    }

    /**
     * Mock should throw exception from latest mock method called.
     *
     * @param mixed $return
     *
     * @return static
     */
    public function andReturn($return): self
    {
        $this->returns[$this->getCurrentConfigIndex()] = new ReturnValue($return);

        return $this;
    }

    /**
     * Mock method call should return self
     *
     * @return static
     */
    public function andReturnSelf(): self
    {
        if ($this->finalInstance !== null) {
            $this->returns[$this->getCurrentConfigIndex()] = new ReturnValue($this->finalInstance);

            return $this;
        }

        $this->returns[$this->getCurrentConfigIndex()] = new ReturnSelf();

        return $this;
    }

    /**
     * Mock should throw exception from latest mock method called.
     *
     * @param string $exception
     *
     * @return static
     */
    public function andThrow(string $exception): self
    {
        $this->exceptions[$this->getCurrentConfigIndex()] = $exception;

        return $this;
    }

    /**
     * @param int $count
     *
     * @return static
     */
    public function times(int $count): self
    {
        $this->times[$this->getCurrentConfigIndex()] = $count;

        return $this;
    }

    /**
     * Get class to mock.
     *
     * @return string
     */
    abstract protected function getClassToMock(): string;

    /**
     * @param \Mockery\ExpectationInterface $expectation
     * @param mixed $return
     *
     * @return void
     */
    private function doReturn(ExpectationInterface $expectation, $return): void
    {
        if ($return instanceof ReturnInterface) {
            $return->doReturn($expectation);

            return;
        }
        $expectation->andReturn($return);
    }

    /**
     * Get available methods that are allowed to be mocked.
     *
     * @return string[]
     */
    private function getAvailableMethods(): array
    {
        return \get_class_methods($this->getClassToMock());
    }

    /**
     * Get latest configuration index.
     *
     * @return int
     */
    private function getCurrentConfigIndex(): int
    {
        return \count($this->configurations) - 1;
    }

    /**
     * Get mock for build.
     *
     * @return \Mockery\MockInterface
     */
    private function getMock(): MockInterface
    {
        return Mockery::mock($this->finalInstance ?? $this->getClassToMock());
    }

    /**
     * Call closure configurations and return mock.
     *
     * @return \Mockery\MockInterface
     */
    final public function build(): MockInterface
    {
        $mock = $this->getMock();

        foreach ($this->configurations as $index => $configuration) {
            $configuration(
                $mock,
                $this->times[$index] ?? null,
                $this->returns[$index] ?? null,
                $this->exceptions[$index] ?? null
            );
        }

        return $mock;
    }
}
