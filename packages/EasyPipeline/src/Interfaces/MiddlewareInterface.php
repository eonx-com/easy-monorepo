<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Interfaces;

interface MiddlewareInterface
{
    public function handle(mixed $input, callable $next): mixed;
}
