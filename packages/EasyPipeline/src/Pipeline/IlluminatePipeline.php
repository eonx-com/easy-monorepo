<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Pipeline;

use EonX\EasyPipeline\Exception\EmptyMiddlewareListException;
use EonX\EasyPipeline\Logger\MiddlewareLoggerAwareInterface;
use EonX\EasyPipeline\Logger\MiddlewareLoggerInterface;
use Illuminate\Contracts\Pipeline\Pipeline as IlluminatePipelineContract;

final class IlluminatePipeline implements PipelineInterface, MiddlewareLoggerInterface
{
    private array $logs = [];

    /**
     * @throws \EonX\EasyPipeline\Exception\EmptyMiddlewareListException
     */
    public function __construct(
        private readonly IlluminatePipelineContract $illuminatePipeline,
        private readonly array $middlewareList,
    ) {
        if (\count($middlewareList) === 0) {
            throw new EmptyMiddlewareListException(\sprintf(
                'In %s, given middleware list is empty',
                self::class
            ));
        }
    }

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
            ->then(static fn ($input) => $input);
    }
}
