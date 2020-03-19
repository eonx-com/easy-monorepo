<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Rules;

use EonX\EasyDecision\Helpers\IfConditionForValue;
use EonX\EasyDecision\Interfaces\ContextAwareInterface;
use EonX\EasyDecision\Interfaces\DecisionOutputForRuleAwareInterface as DecisionOutputAware;
use EonX\EasyDecision\Interfaces\ExpressionLanguageAwareInterface as ExprLangAware;
use EonX\EasyDecision\Interfaces\RuleInterface;
use EonX\EasyDecision\Traits\ContextAwareTrait;
use EonX\EasyDecision\Traits\ExpressionLanguageAwareTrait;

final class ExpressionLanguageRule implements RuleInterface, ContextAwareInterface, ExprLangAware, DecisionOutputAware
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
     * @param mixed $decisionOutput
     *
     * @return mixed
     */
    public function getDecisionOutputForRule($decisionOutput)
    {
        if ($this->extra === null) {
            return $decisionOutput;
        }

        $this->extra['output'] = $decisionOutput;

        return $this->extra;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
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
     * @param mixed[] $input
     */
    public function supports(array $input): bool
    {
        return true;
    }

    public function toString(): string
    {
        return $this->name ?? $this->expression;
    }

    /**
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
