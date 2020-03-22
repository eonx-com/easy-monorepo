<?php

declare(strict_types=1);

namespace EonX\EasyPipeline\Interfaces;

interface MiddlewareLoggerInterface
{
    /**
     * @param mixed $content
     */
    public function log(string $middleware, $content): void;
}
