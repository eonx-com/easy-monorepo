<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Decisions;

use EonX\EasyDecision\Context;
use EonX\EasyDecision\Exceptions\ContextNotSetException;
use EonX\EasyDecision\Exceptions\ExpressionLanguageNotSetOnDecisionException;
use EonX\EasyDecision\Exceptions\ReservedContextIndexException;
use EonX\EasyDecision\Exceptions\UnableToMakeDecisionException;
use EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageInterface;
use EonX\EasyDecision\Interfaces\ContextAwareInterface;
use EonX\EasyDecision\Interfaces\ContextInterface;
use EonX\EasyDecision\Interfaces\DecisionInterface;
use EonX\EasyDecision\Interfaces\DecisionOutputForRuleAwareInterface;
use EonX\EasyDecision\Interfaces\ExpressionLanguageAwareInterface;
use EonX\EasyDecision\Interfaces\NonBlockingRuleErrorInterface;
use EonX\EasyDecision\Interfaces\RuleInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;
use Throwable;

abstract class AbstractDecision implements DecisionInterface
{
    protected ?ContextInterface $context = null;

    protected array $input;

    private mixed $defaultOutput;

    private bool $exitOnPropagationStopped = false;

    private ?ExpressionLanguageInterface $expressionLanguage = null;

    private string $name;

    /**
     * @var \EonX\EasyDecision\Interfaces\RuleInterface[]
     */
    private array $rules = [];

    public function __construct(?string $name = null)
    {
        $this->name = $name ?? '<no-name>';
    }

    public function addRule(RuleInterface $rule): DecisionInterface
    {
        $this->rules[] = $rule;

        return $this;
    }

    /**
     * @param \EonX\EasyDecision\Interfaces\RuleInterface[] $rules
     */
    public function addRules(array $rules): DecisionInterface
    {
        foreach ($rules as $rule) {
            $this->addRule($rule);
        }

        return $this;
    }

    public function getContext(): ContextInterface
    {
        if ($this->context !== null) {
            return $this->context;
        }

        throw new ContextNotSetException($this->getExceptionMessage(
            'You cannot called getContext() before decision has been made'
        ));
    }

    public function getExpressionLanguage(): ?ExpressionLanguageInterface
    {
        return $this->expressionLanguage;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @throws \EonX\EasyDecision\Exceptions\InvalidArgumentException
     * @throws \EonX\EasyDecision\Exceptions\UnableToMakeDecisionException
     */
    public function make(array $input): mixed
    {
        // Reset decision before each make, so a single decision instance can be used more than once
        $this->reset();

        // Index "context" cannot be used by users to avoid conflicts
        // because context is injected in expression language rules
        if (isset($input['context'])) {
            throw new ReservedContextIndexException($this->getExceptionMessage(
                '"context" is a reserved index it cannot be used'
            ));
        }

        $this->input = $input;
        $this->context = new Context(static::class, $input);

        // If no rules provided, return default output
        if (\count($this->rules) === 0) {
            return $this->defaultOutput ?? $this->getDefaultOutput();
        }

        try {
            // Let children classes handle rules output and define the output
            return $this->processRules()
                ->doMake();
        } catch (Throwable $exception) {
            throw new UnableToMakeDecisionException(
                $this->getExceptionMessage($exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    public function setDefaultOutput(mixed $defaultOutput = null): DecisionInterface
    {
        $this->defaultOutput = $defaultOutput;

        return $this;
    }

    public function setExitOnPropagationStopped(?bool $exit = null): DecisionInterface
    {
        $this->exitOnPropagationStopped = $exit ?? true;

        return $this;
    }

    public function setExpressionLanguage(ExpressionLanguageInterface $expressionLanguage): DecisionInterface
    {
        $this->expressionLanguage = $expressionLanguage;

        return $this;
    }

    public function setName(string $name): DecisionInterface
    {
        $this->name = $name;

        return $this;
    }

    abstract protected function doHandleRuleOutput(mixed $output): void;

    abstract protected function doMake(): mixed;

    abstract protected function getDefaultOutput(): mixed;

    abstract protected function reset(): void;

    protected function getExceptionMessage(string $message): string
    {
        return \sprintf('Decision "%s" of type "%s": %s', $this->name, static::class, $message);
    }

    private function addDecisionOutputForRule(RuleInterface $rule, mixed $output): void
    {
        // Allow rules to customise decision output
        if ($rule instanceof DecisionOutputForRuleAwareInterface) {
            $output = $rule->getDecisionOutputForRule($output);
        }

        $this->getContext()
            ->addRuleOutput($rule->toString(), $output);
    }

    private function getExpressionLanguageForRule(): ExpressionLanguageInterface
    {
        if ($this->expressionLanguage !== null) {
            return $this->expressionLanguage;
        }

        throw new ExpressionLanguageNotSetOnDecisionException(
            'Expression language not set, to use it in your rules you must set it on the decision instance'
        );
    }

    /**
     * @return iterable<\EonX\EasyDecision\Interfaces\RuleInterface>
     */
    private function getRules(): iterable
    {
        foreach (CollectorHelper::orderLowerPriorityFirstAsArray($this->rules) as $rule) {
            if ($rule instanceof ContextAwareInterface) {
                $rule->setContext($this->getContext());
            }

            if ($rule instanceof ExpressionLanguageAwareInterface) {
                $rule->setExpressionLanguage($this->getExpressionLanguageForRule());
            }

            yield $rule;
        }
    }

    private function processRules(): self
    {
        foreach ($this->getRules() as $rule) {
            // If propagation stopped, skip all the rules
            if ($this->getContext()->isPropagationStopped()) {
                // If exit on propagation stopped true, stop processing rules
                if ($this->exitOnPropagationStopped) {
                    break;
                }

                $this->addDecisionOutputForRule($rule, RuleInterface::OUTPUT_SKIPPED);

                continue;
            }

            // If rule doesn't support the input
            if ($rule->supports($this->input) === false) {
                $this->addDecisionOutputForRule($rule, RuleInterface::OUTPUT_UNSUPPORTED);

                continue;
            }

            try {
                $ruleOutput = $rule->proceed($this->input);

                $this->addDecisionOutputForRule($rule, $ruleOutput);

                // Let children classes handle the rule output
                $this->doHandleRuleOutput($ruleOutput);
            } catch (NonBlockingRuleErrorInterface $exception) {
                $this->addDecisionOutputForRule($rule, $exception->getErrorOutput());
            }
        }

        return $this;
    }
}
