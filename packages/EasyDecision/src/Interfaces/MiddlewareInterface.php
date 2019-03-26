<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Interfaces;

interface MiddlewareInterface
{
    /**
     * Handle given context input and pass return through next.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\ContextInterface $context
     * @param callable $next
     *
     * @return mixed
     */
    public function handle(ContextInterface $context, callable $next);
}
