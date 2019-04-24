<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Interfaces;

interface MiddlewareInterface
{
    /**
     * Handle given context input and pass return through next.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\ContextInterface $context
     * @param callable $next
     *
     * @return mixed
     */
    public function handle(ContextInterface $context, callable $next);
}

\class_alias(
    MiddlewareInterface::class,
    'StepTheFkUp\EasyDecision\Interfaces\MiddlewareInterface',
    false
);
