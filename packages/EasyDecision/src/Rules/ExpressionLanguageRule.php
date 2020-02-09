<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Rules;

use EonX\EasyDecision\Interfaces\ContextAwareInterface;
use EonX\EasyDecision\Interfaces\ExpressionLanguageAwareInterface;
use EonX\EasyDecision\Interfaces\RuleInterface;
use EonX\EasyDecision\Traits\ContextAwareTrait;
use EonX\EasyDecision\Traits\ExpressionLanguageAwareTrait;

final class ExpressionLanguageRule implements RuleInterface, ContextAwareInterface, ExpressionLanguageAwareInterface
{
    use ContextAwareTrait;
    use ExpressionLanguageAwareTrait;

    /**
     * @var string
     */
    private $expression;

    /**
     * @var null|string
     */
    private $name;

    /**
     * @var int
     */
    private $priority;

    /**
     * ExpressionLanguageRule constructor.
     *
     * @param string $expression
     * @param null|int $priority
     * @param null|string $name
     */
    public function __construct(string $expression, ?int $priority = null, ?string $name = null)
    {
        $this->expression = $expression;
        $this->priority = $priority ?? 0;
        $this->name = $name;
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
        return $this->name ?? $this->expression;
    }
}
