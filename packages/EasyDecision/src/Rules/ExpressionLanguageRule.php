<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Rules;

use LoyaltyCorp\EasyDecision\Interfaces\ContextAwareInterface;
use LoyaltyCorp\EasyDecision\Interfaces\ExpressionLanguageAwareInterface;
use LoyaltyCorp\EasyDecision\Interfaces\RuleInterface;
use LoyaltyCorp\EasyDecision\Traits\ContextAwareTrait;
use LoyaltyCorp\EasyDecision\Traits\ExpressionLanguageAwareTrait;

final class ExpressionLanguageRule implements RuleInterface, ContextAwareInterface, ExpressionLanguageAwareInterface
{
    use ContextAwareTrait;
    use ExpressionLanguageAwareTrait;

    /**
     * @var string
     */
    private $expression;

    /**
     * @var int
     */
    private $priority;

    /**
     * ExpressionLanguageRule constructor.
     *
     * @param string $expression
     * @param null|int $priority
     */
    public function __construct(string $expression, ?int $priority = null)
    {
        $this->expression = $expression;
        $this->priority = $priority ?? 0;
    }

    /**
     * Get priority.
     *
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Proceed with input.
     *
     * @param mixed[] $input
     *
     * @return mixed
     */
    public function proceed(array $input)
    {
        $input['context'] = $this->context;

        return $this->expressionLanguage->evaluate($this->expression, $input);
    }

    /**
     * Check if rule supports given input.
     *
     * @param mixed[] $input
     *
     * @return bool
     */
    public function supports(array $input): bool
    {
        return true;
    }

    /**
     * Get string representation of the rule.
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->expression;
    }
}

\class_alias(
    ExpressionLanguageRule::class,
    'StepTheFkUp\EasyDecision\Rules\ExpressionLanguageRule',
    false
);
