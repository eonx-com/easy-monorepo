<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Interfaces;

interface DecisionInterface
{
    /**
     * @var string[]
     */
    public const TYPES_YESNO = [
        self::TYPE_YESNO_AFFIRMATIVE,
        self::TYPE_YESNO_CONSENSUS,
        self::TYPE_YESNO_UNANIMOUS
    ];

    /**
     * @var string
     */
    public const TYPE_VALUE = 'value';

    /**
     * @var string
     */
    public const TYPE_YESNO_AFFIRMATIVE = 'yesno_affirmative';

    /**
     * @var string
     */
    public const TYPE_YESNO_CONSENSUS = 'yesno_consensus';

    /**
     * @var string
     */
    public const TYPE_YESNO_UNANIMOUS = 'yesno_unanimous';

    /**
     * Add rule.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\RuleInterface $rule
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\DecisionInterface
     */
    public function addRule(RuleInterface $rule): self;

    /**
     * Set rules.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\RuleInterface[] $rules
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\DecisionInterface
     */
    public function addRules(array $rules): self;

    /**
     * Get context.
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\ContextInterface
     *
     * @throws \StepTheFkUp\EasyDecision\Exceptions\ContextNotSetException
     */
    public function getContext(): ContextInterface;

    /**
     * Make value decision for given input.
     *
     * @param mixed $input
     *
     * @return mixed
     *
     * @throws \StepTheFkUp\EasyDecision\Exceptions\InvalidArgumentException
     * @throws \StepTheFkUp\EasyDecision\Exceptions\UnableToMakeDecisionException
     */
    public function make($input);
}
