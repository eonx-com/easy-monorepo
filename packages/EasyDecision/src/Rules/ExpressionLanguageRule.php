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

    private int $priority;

    /**
     * @param null|mixed[] $extra
     */
    public function __construct(
        private string $expression,
        ?int $priority = null,
        private ?string $name = null,
        private ?array $extra = null,
    ) {
        $this->priority = $priority ?? 0;
    }

    public function getDecisionOutputForRule(mixed $decisionOutput): mixed
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
     */
    public function proceed(array $input): mixed
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
     */
    private function getOutput(array $input): mixed
    {
        $output = $this->expressionLanguage->evaluate($this->expression, $input);

        return $output instanceof IfConditionForValue ? $output->getValue() : $output;
    }
}
