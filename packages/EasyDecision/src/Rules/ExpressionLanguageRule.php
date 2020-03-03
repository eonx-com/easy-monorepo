<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Rules;

use EonX\EasyDecision\Helpers\IfConditionForValue;
use EonX\EasyDecision\Interfaces\ContextAwareInterface;
use EonX\EasyDecision\Interfaces\DecisionOutputForRuleAwareInterface;
use EonX\EasyDecision\Interfaces\ExpressionLanguageAwareInterface;
use EonX\EasyDecision\Interfaces\RuleInterface;
use EonX\EasyDecision\Traits\ContextAwareTrait;
use EonX\EasyDecision\Traits\ExpressionLanguageAwareTrait;

final class ExpressionLanguageRule implements RuleInterface, ContextAwareInterface, ExpressionLanguageAwareInterface, DecisionOutputForRuleAwareInterface
{
    use ContextAwareTrait;
    use ExpressionLanguageAwareTrait;

    /**
     * @var string
     */
    private $expression;

    /**
     * @var null|mixed[]
     */
    private $extra;

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
     * @param null|mixed[] $extra
     */
    public function __construct(string $expression, ?int $priority = null, ?string $name = null, ?array $extra = null)
    {
        $this->expression = $expression;
        $this->priority = $priority ?? 0;
        $this->name = $name;
        $this->extra = $extra;
    }

    /**
     * @inheritDoc
     */
    public function getDecisionOutputForRule($decisionOutput)
    {
        if ($this->extra === null) {
            return $decisionOutput;
        }

        $this->extra['output'] = $decisionOutput;

        return $this->extra;
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

        return $this->getOutput($input);
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

    /**
     * Get output for given input, handle if condition for value.
     *
     * @param mixed[] $input
     *
     * @return mixed
     */
    private function getOutput(array $input)
    {
        $output = $this->expressionLanguage->evaluate($this->expression, $input);

        return $output instanceof IfConditionForValue ? $output->getValue() : $output;
    }
}
