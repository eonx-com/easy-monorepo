<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Decisions;

use EonX\EasyDecision\Context;
use EonX\EasyDecision\Exceptions\ContextNotSetException;
use EonX\EasyDecision\Exceptions\EmptyRulesException;
use EonX\EasyDecision\Exceptions\ReservedContextIndexException;
use EonX\EasyDecision\Exceptions\UnableToMakeDecisionException;
use EonX\EasyDecision\Interfaces\ContextAwareInterface;
use EonX\EasyDecision\Interfaces\ContextInterface;
use EonX\EasyDecision\Interfaces\DecisionInterface;
use EonX\EasyDecision\Interfaces\NonBlockingRuleErrorInterface;
use EonX\EasyDecision\Interfaces\RuleInterface;

abstract class AbstractDecision implements DecisionInterface
{
    /** @var \EonX\EasyDecision\Interfaces\ContextInterface */
    private $context;

    /** @var mixed[] */
    private $input;

    /** @var string */
    private $name;

    /** @var \EonX\EasyDecision\Interfaces\RuleInterface[] */
    private $rules = [];

    /**
     * NewAbstractDecision constructor.
     *
     * @param string $name
     */
    public function __construct(?string $name = null)
    {
        $this->name = $name;
    }

    /**
     * Add rule.
     *
     * @param \EonX\EasyDecision\Interfaces\RuleInterface $rule
     *
     * @return \EonX\EasyDecision\Interfaces\DecisionInterface
     */
    public function addRule(RuleInterface $rule): DecisionInterface
    {
        $this->rules[] = $rule;

        return $this;
    }

    /**
     * Set rules.
     *
     * @param \EonX\EasyDecision\Interfaces\RuleInterface[] $rules
     *
     * @return \EonX\EasyDecision\Interfaces\DecisionInterface
     */
    public function addRules(array $rules): DecisionInterface
    {
        foreach ($rules as $rule) {
            $this->addRule($rule);
        }

        return $this;
    }

    /**
     * Get context.
     *
     * @return \EonX\EasyDecision\Interfaces\ContextInterface
     *
     * @throws \EonX\EasyDecision\Exceptions\ContextNotSetException
     */
    public function getContext(): ContextInterface
    {
        if ($this->context !== null) {
            return $this->context;
        }

        throw new ContextNotSetException($this->getExceptionMessage(
            'You cannot called getContext() before decision has been made'
        ));
    }

    /**
     * Get decision name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

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
    public function make(array $input)
    {
        // Cannot make decision with no rules
        if (empty($this->rules)) {
            throw new EmptyRulesException($this->getExceptionMessage('cannot be made with no rules'));
        }

        // Index "context" cannot be used by users to avoid conflicts
        // because context is injected in expression language rules
        if (isset($input['context'])) {
            throw new ReservedContextIndexException($this->getExceptionMessage(
                '"context" is a reserved index it cannot be used'
            ));
        }

        $this->input = $input;
        $this->context = $context = new Context(\get_class($this), $input);

        try {
            // Let children classes handle rules output and define the output
            return $this->processRules($context)->doMake();
        } catch (\Exception $exception) {
            throw new UnableToMakeDecisionException(
                $this->getExceptionMessage($exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * Set decision name.
     *
     * @param string $name
     *
     * @return \EonX\EasyDecision\Interfaces\DecisionInterface
     */
    public function setName(string $name): DecisionInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Let children classes make the decision.
     *
     * @return mixed
     */
    abstract protected function doMake();

    /**
     * Handle rule output.
     *
     * @param \EonX\EasyDecision\Interfaces\ContextInterface $context
     * @param string $rule
     * @param mixed $output
     *
     * @return void
     */
    abstract protected function handleRuleOutput(ContextInterface $context, string $rule, $output): void;

    /**
     * Get prefixed message for exception.
     *
     * @param string $message
     *
     * @return string
     */
    protected function getExceptionMessage(string $message): string
    {
        return \sprintf('Decision "%s" of type "%s": %s', $this->name, \get_class($this), $message);
    }

    /**
     * Update current input with given one.
     *
     * @param mixed[] $input
     *
     * @return void
     */
    protected function updateInput(array $input): void
    {
        $this->input = $input + $this->input;
    }

    /**
     * Get sorted rules (priority value 0 is higher than 100).
     *
     * @param \EonX\EasyDecision\Interfaces\ContextInterface $context
     *
     * @return \EonX\EasyDecision\Interfaces\RuleInterface[]
     */
    private function getRules(ContextInterface $context): array
    {
        // Sort rules by priority
        $rules = $this->rules;
        \usort($rules, function (RuleInterface $first, RuleInterface $second): int {
            return $first->getPriority() <=> $second->getPriority();
        });

        foreach ($rules as $rule) {
            if ($rule instanceof ContextAwareInterface) {
                $rule->setContext($context);
            }
        }

        return $rules;
    }

    /**
     * Process rules for given context.
     *
     * @param \EonX\EasyDecision\Interfaces\ContextInterface $context
     *
     * @return self
     */
    private function processRules(ContextInterface $context): self
    {
        foreach ($this->getRules($context) as $rule) {
            // If propagation stopped, skip all the rules
            if ($context->isPropagationStopped()) {
                $context->addRuleOutput($rule->toString(), RuleInterface::OUTPUT_SKIPPED);
                continue;
            }

            // If rule doesn't support the input
            if ($rule->supports($this->input) === false) {
                $context->addRuleOutput($rule->toString(), RuleInterface::OUTPUT_UNSUPPORTED);
                continue;
            }

            try {
                // Let children classes handle the rule output
                $this->handleRuleOutput($context, $rule->toString(), $rule->proceed($this->input));
            } catch (NonBlockingRuleErrorInterface $exception) {
                $context->addRuleOutput($rule->toString(), $exception->getErrorOutput());
            }
        }

        return $this;
    }
}
