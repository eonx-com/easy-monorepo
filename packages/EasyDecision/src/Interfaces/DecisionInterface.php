<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

use EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageInterface;

interface DecisionInterface
{
    public function addRule(RuleInterface $rule): self;

    /**
     * @param \EonX\EasyDecision\Interfaces\RuleInterface[] $rules
     */
    public function addRules(array $rules): self;

    public function getContext(): ContextInterface;

    public function getExpressionLanguage(): ?ExpressionLanguageInterface;

    public function getName(): string;

    /**
     * @throws \EonX\EasyDecision\Exceptions\InvalidArgumentException
     * @throws \EonX\EasyDecision\Exceptions\UnableToMakeDecisionException
     */
    public function make(array $input): mixed;

    public function setDefaultOutput(mixed $defaultOutput = null): self;

    public function setExitOnPropagationStopped(?bool $exit = null): self;

    public function setExpressionLanguage(ExpressionLanguageInterface $expressionLanguage): self;

    public function setName(string $name): self;
}
