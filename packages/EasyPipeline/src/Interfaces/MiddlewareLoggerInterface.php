<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Interfaces;

interface MiddlewareLoggerInterface
{
    /**
     * Return logs created by each middleware during process.
     *
     * @return mixed[]
     *
     * @throws \StepTheFkUp\EasyPipeline\Exceptions\PipelineDidntRunException If called before process() is called
     */
    public function getLogs(): array;

    /**
     * Log given content for given middleware.
     *
     * @param string $middleware
     * @param mixed $content
     *
     * @return void
     */
    public function log(string $middleware, $content): void;
}
