<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Rule;

use EonX\EasyDecision\Context\ContextAwareInterface;
use EonX\EasyDecision\Context\ContextAwareTrait;
use EonX\EasyDecision\ExpressionLanguage\ExpressionLanguageAwareInterface as ExprLangAware;
use EonX\EasyDecision\ExpressionLanguage\ExpressionLanguageAwareTrait;
use EonX\EasyDecision\Helper\IfConditionForValueHelper;
use EonX\EasyDecision\Rule\DecisionOutputForRuleAwareInterface as DecisionOutputAware;

final class ExpressionLanguageRule implements RuleInterface, ContextAwareInterface, ExprLangAware, DecisionOutputAware
{
    use ContextAwareTrait;
    use ExpressionLanguageAwareTrait;

    private int $priority;

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

    public function proceed(array $input): mixed
    {
        $input['context'] = $this->context;

        return $this->getOutput($input);
    }

    public function supports(array $input): bool
    {
        return true;
    }

    public function toString(): string
    {
        return $this->name ?? $this->expression;
    }

    private function getOutput(array $input): mixed
    {
        $output = $this->expressionLanguage->evaluate($this->expression, $input);

        return $output instanceof IfConditionForValueHelper ? $output->getValue() : $output;
    }
}
