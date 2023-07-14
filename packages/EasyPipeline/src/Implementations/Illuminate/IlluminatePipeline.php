<?php

declare(strict_types=1);

namespace EonX\EasyPipeline\Implementations\Illuminate;

use EonX\EasyPipeline\Exceptions\EmptyMiddlewareListException;
use EonX\EasyPipeline\Interfaces\MiddlewareLoggerAwareInterface;
use EonX\EasyPipeline\Interfaces\MiddlewareLoggerInterface;
use EonX\EasyPipeline\Interfaces\PipelineInterface;
use Illuminate\Contracts\Pipeline\Pipeline as IlluminatePipelineContract;

final class IlluminatePipeline implements PipelineInterface, MiddlewareLoggerInterface
{
    /**
     * @var mixed[]
     */
    private array $logs = [];

    /**
     * @param mixed[] $middlewareList
     *
     * @throws \EonX\EasyPipeline\Exceptions\EmptyMiddlewareListException
     */
    public function __construct(
        private IlluminatePipelineContract $illuminatePipeline,
        private array $middlewareList,
    ) {
        if (\count($middlewareList) === 0) {
            throw new EmptyMiddlewareListException(\sprintf(
                'In %s, given middleware list is empty',
                self::class
            ));
        }
    }

    /**
     * @return mixed[]
     */
    public function getLogs(): array
    {
        return $this->logs;
    }

    public function log(string $middleware, mixed $content): void
    {
        if (isset($this->logs[$middleware]) === false) {
            $this->logs[$middleware] = [];
        }

        $this->logs[$middleware][] = $content;
    }

    public function process(mixed $input): mixed
    {
        // Reset logs to allow same pipeline to process multiple inputs
        $this->logs = [];

        // Handle middleware logger aware
        foreach ($this->middlewareList as $middleware) {
            if ($middleware instanceof MiddlewareLoggerAwareInterface) {
                $middleware->setLogger($this);
            }
        }

        return $this->illuminatePipeline
            ->send($input)
            ->through($this->middlewareList)
            ->via('handle')
            ->then(function ($input) {
                return $input;
            });
    }
}
