<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Middleware;

use LoyaltyCorp\EasyDecision\Interfaces\ContextInterface;
use LoyaltyCorp\EasyDecision\Interfaces\MiddlewareInterface;
use LoyaltyCorp\EasyDecision\Interfaces\RuleInterface;

abstract class AbstractMiddleware implements MiddlewareInterface
{
    /**
     * @var \LoyaltyCorp\EasyDecision\Interfaces\RuleInterface
     */
    protected $rule;

    /**
     * AbstractMiddleware constructor.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\RuleInterface $rule
     */
    public function __construct(RuleInterface $rule)
    {
        $this->rule = $rule;
    }

    /**
     * Handle given context input and pass return through next.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\ContextInterface $context
     * @param callable $next
     *
     * @return mixed
     */
    public function handle(ContextInterface $context, callable $next)
    {
        if ($context->isPropagationStopped()) {
            return $this->abort($context, $next, RuleInterface::OUTPUT_SKIPPED);
        }

        if ($this->rule->supports($context) === false) {
            return $this->abort($context, $next, RuleInterface::OUTPUT_UNSUPPORTED);
        }

        $this->doHandle($context, $this->rule->proceed($context));

        return $next($context);
    }

    /**
     * Make sure children classes handle given context.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\ContextInterface $context
     * @param mixed $output
     *
     * @return void
     */
    abstract protected function doHandle(ContextInterface $context, $output): void;

    /**
     * Add given rule output to given context.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\ContextInterface $context
     * @param mixed $output
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\ContextInterface
     */
    protected function addRuleOutput(ContextInterface $context, $output): ContextInterface
    {
        return $context->addRuleOutput($this->rule->toString(), $output);
    }

    /**
     * Abort rule.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\ContextInterface $context
     * @param callable $next
     * @param string $reason
     *
     * @return mixed
     */
    private function abort(ContextInterface $context, callable $next, string $reason)
    {
        $this->addRuleOutput($context, $reason);

        return $next($context);
    }
}

\class_alias(
    AbstractMiddleware::class,
    'StepTheFkUp\EasyDecision\Middleware\AbstractMiddleware',
    false
);
