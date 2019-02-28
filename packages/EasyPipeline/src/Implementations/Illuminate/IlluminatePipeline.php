<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Implementations\Illuminate;

use StepTheFkUp\EasyPipeline\Exceptions\PipelineDidntRunException;
use StepTheFkUp\EasyPipeline\Interfaces\MiddlewareLoggerAwareInterface;
use StepTheFkUp\EasyPipeline\Interfaces\MiddlewareLoggerInterface;
use StepTheFkUp\EasyPipeline\Interfaces\PipelineInterface;
use Illuminate\Contracts\Pipeline\Pipeline as IlluminatePipelineContract;

final class IlluminatePipeline implements PipelineInterface, MiddlewareLoggerInterface
{
    /**
     * @var \Illuminate\Contracts\Pipeline\Pipeline
     */
    private $illuminatePipeline;

    /**
     * @var mixed
     */
    private $input;

    /**
     * @var mixed[]
     */
    private $logs = [];

    /**
     * @var mixed[]
     */
    private $middlewareList;

    /**
     * @var bool
     */
    private $ran = false;

    /**
     * IlluminatePipeline constructor.
     *
     * @param \Illuminate\Contracts\Pipeline\Pipeline $illuminatePipeline
     */
    public function __construct(IlluminatePipelineContract $illuminatePipeline)
    {
        $this->illuminatePipeline = $illuminatePipeline;
    }

    /**
     * Return logs created by each middleware during process.
     *
     * @return mixed[]
     *
     * @throws \StepTheFkUp\EasyPipeline\Exceptions\PipelineDidntRunException If called before process() is called
     */
    public function getLogs(): array
    {
        if ($this->ran === false) {
            throw new PipelineDidntRunException('getLogs() cannot be called on a pipeline which did not run');
        }

        return $this->logs;
    }

    /**
     * Log given content for given middleware.
     *
     * @param string $middleware
     * @param mixed $content
     *
     * @return void
     */
    public function log(string $middleware, $content): void
    {
        if (isset($this->logs[$middleware]) === false) {
            $this->logs[$middleware] = [];
        }

        $this->logs[$middleware][] = $content;
    }

    /**
     * Process set input through set middleware list and return processed input.
     *
     * @return mixed
     */
    public function process()
    {
        // Handle middleware logger aware
        foreach ($this->middlewareList as $middleware) {
            if ($middleware instanceof MiddlewareLoggerAwareInterface) {
                $middleware->setLogger($this);
            }
        }

        $processed = $this->illuminatePipeline
            ->send($this->input)
            ->through($this->middlewareList)
            ->via('handle')
            ->then($this->getPassClosure());

        $this->ran = true;

        return $processed;
    }

    /**
     * Set input to be processed.
     *
     * @param mixed $input
     *
     * @return \StepTheFkUp\EasyPipeline\Interfaces\PipelineInterface
     */
    public function setInput($input): PipelineInterface
    {
        $this->ran = false;
        $this->input = $input;

        return $this;
    }

    /**
     * Set middleware list to process input with.
     *
     * @param mixed[] $middlewareList
     *
     * @return \StepTheFkUp\EasyPipeline\Interfaces\PipelineInterface
     */
    public function setMiddlewareList(array $middlewareList): PipelineInterface
    {
        $this->ran = false;
        $this->middlewareList = $middlewareList;

        return $this;
    }

    /**
     * Get closure to simply return given input.
     *
     * @return \Closure
     */
    private function getPassClosure(): \Closure
    {
        return function ($input) {
            return $input;
        };
    }
}
