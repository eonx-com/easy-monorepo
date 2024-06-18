<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Decision;

use EonX\EasyDecision\Context\ContextInterface;
use EonX\EasyDecision\ExpressionLanguage\ExpressionLanguageInterface;
use EonX\EasyDecision\Rule\RuleInterface;

interface DecisionInterface
{
    public function addRule(RuleInterface $rule): self;

    /**
     * @param \EonX\EasyDecision\Rule\RuleInterface[] $rules
     */
    public function addRules(array $rules): self;

    public function getContext(): ContextInterface;

    public function getExpressionLanguage(): ?ExpressionLanguageInterface;

    public function getName(): string;

    /**
     * @throws \EonX\EasyDecision\Exception\InvalidArgumentException
     * @throws \EonX\EasyDecision\Exception\UnableToMakeDecisionException
     */
    public function make(array $input): mixed;

    public function setDefaultOutput(mixed $defaultOutput = null): self;

    public function setExitOnPropagationStopped(?bool $exit = null): self;

    public function setExpressionLanguage(ExpressionLanguageInterface $expressionLanguage): self;

    public function setName(string $name): self;
}
