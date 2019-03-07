<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Interfaces;

interface MiddlewareInterface
{
    /**
     * Handle given input and pass return through next.
     *
     * @param mixed $input
     * @param callable $next
     *
     * @return mixed
     */
    public function handle($input, callable $next);
}
