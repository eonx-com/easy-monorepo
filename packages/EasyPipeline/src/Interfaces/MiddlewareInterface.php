<?php

declare(strict_types=1);

namespace EonX\EasyPipeline\Interfaces;

interface MiddlewareInterface
{
    /**
     * @param mixed $input
     *
     * @return mixed
     */
    public function handle($input, callable $next);
}
