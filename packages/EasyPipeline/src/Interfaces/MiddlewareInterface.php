<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPipeline\Interfaces;

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

\class_alias(
    MiddlewareInterface::class,
    'StepTheFkUp\EasyPipeline\Interfaces\MiddlewareInterface',
    false
);
