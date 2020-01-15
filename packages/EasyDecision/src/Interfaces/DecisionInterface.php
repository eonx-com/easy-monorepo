<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

interface DecisionInterface
{
    /**
     * Add rule.
     *
     * @param \EonX\EasyDecision\Interfaces\RuleInterface $rule
     *
     * @return \EonX\EasyDecision\Interfaces\DecisionInterface
     */
    public function addRule(RuleInterface $rule): self;

    /**
     * Set rules.
     *
     * @param \EonX\EasyDecision\Interfaces\RuleInterface[] $rules
     *
     * @return \EonX\EasyDecision\Interfaces\DecisionInterface
     */
    public function addRules(array $rules): self;

    /**
     * Get context.
     *
     * @return \EonX\EasyDecision\Interfaces\ContextInterface
     *
     * @throws \EonX\EasyDecision\Exceptions\ContextNotSetException
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
     * @throws \EonX\EasyDecision\Exceptions\EmptyRulesException
     * @throws \EonX\EasyDecision\Exceptions\InvalidArgumentException
     * @throws \EonX\EasyDecision\Exceptions\UnableToMakeDecisionException
     */
    public function make(array $input);

    /**
     * Set decision name.
     *
     * @param string $name
     *
     * @return \EonX\EasyDecision\Interfaces\DecisionInterface
     */
    public function setName(string $name): self;
}
