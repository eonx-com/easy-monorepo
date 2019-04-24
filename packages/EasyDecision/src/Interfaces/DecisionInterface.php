<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Interfaces;

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
     * Make value decision for given input.
     *
     * @param mixed $input
     *
     * @return mixed
     *
     * @throws \LoyaltyCorp\EasyDecision\Exceptions\InvalidArgumentException
     * @throws \LoyaltyCorp\EasyDecision\Exceptions\UnableToMakeDecisionException
     */
    public function make($input);
}

\class_alias(
    DecisionInterface::class,
    'StepTheFkUp\EasyDecision\Interfaces\DecisionInterface',
    false
);
