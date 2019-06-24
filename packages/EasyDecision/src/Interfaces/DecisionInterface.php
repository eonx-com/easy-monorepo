<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Interfaces;

interface DecisionInterface
{
    /**
     * Add rule.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\RuleInterface $rule
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\DecisionInterface
     */
    public function addRule(RuleInterface $rule): self;

    /**
     * Set rules.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\RuleInterface[] $rules
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\DecisionInterface
     */
    public function addRules(array $rules): self;

    /**
     * Get context.
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\ContextInterface
     *
     * @throws \LoyaltyCorp\EasyDecision\Exceptions\ContextNotSetException
     */
    public function getContext(): ContextInterface;

    /**
     * Get decision name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Make value decision for given array input.
     *
     * @param mixed[] $input
     *
     * @return mixed
     *
     * @throws \LoyaltyCorp\EasyDecision\Exceptions\EmptyRulesException
     * @throws \LoyaltyCorp\EasyDecision\Exceptions\InvalidArgumentException
     * @throws \LoyaltyCorp\EasyDecision\Exceptions\UnableToMakeDecisionException
     */
    public function make(array $input);

    /**
     * Set decision name.
     *
     * @param string $name
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\DecisionInterface
     */
    public function setName(string $name): self;
}

\class_alias(
    DecisionInterface::class,
    'StepTheFkUp\EasyDecision\Interfaces\DecisionInterface',
    false
);
