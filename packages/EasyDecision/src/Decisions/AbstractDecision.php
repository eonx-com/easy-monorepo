<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Decisions;

use Illuminate\Pipeline\Pipeline as BaseIlluminatePipeline;
use StepTheFkUp\EasyDecision\Context;
use StepTheFkUp\EasyDecision\Exceptions\ContextNotSetException;
use StepTheFkUp\EasyDecision\Exceptions\EmptyRulesException;
use StepTheFkUp\EasyDecision\Exceptions\ReservedContextIndexException;
use StepTheFkUp\EasyDecision\Exceptions\UnableToMakeDecisionException;
use StepTheFkUp\EasyDecision\Interfaces\ContextAwareInterface;
use StepTheFkUp\EasyDecision\Interfaces\ContextInterface;
use StepTheFkUp\EasyDecision\Interfaces\DecisionInterface;
use StepTheFkUp\EasyDecision\Interfaces\RuleInterface;
use StepTheFkUp\EasyDecision\Middleware\ValueMiddleware;
use StepTheFkUp\EasyDecision\Middleware\YesNoMiddleware;
use StepTheFkUp\EasyPipeline\Implementations\Illuminate\IlluminatePipeline;
use StepTheFkUp\EasyPipeline\Interfaces\PipelineInterface;

abstract class AbstractDecision implements DecisionInterface
{
    /**
     * @var \StepTheFkUp\EasyDecision\Interfaces\ContextInterface
     */
    private $context;

    /**
     * @var \StepTheFkUp\EasyDecision\Interfaces\RuleInterface[]
     */
    private $rules = [];

    /**
     * Add rule.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\RuleInterface $rule
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\DecisionInterface
     */
    public function addRule(RuleInterface $rule): DecisionInterface
    {
        $this->rules[] = $rule;

        return $this;
    }

    /**
     * Validate each rule is an instance of RuleInterface and sort them by priority.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\RuleInterface[] $rules
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\DecisionInterface
     *
     * @throws \StepTheFkUp\EasyDecision\Exceptions\InvalidArgumentException
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
     * @return \StepTheFkUp\EasyDecision\Interfaces\ContextInterface
     *
     * @throws \StepTheFkUp\EasyDecision\Exceptions\ContextNotSetException
     */
    public function getContext(): ContextInterface
    {
        if ($this->context !== null) {
            return $this->context;
        }

        throw new ContextNotSetException('You cannot called getContext() before decision has been made');
    }

    /**
     * Make value decision for given input.
     *
     * @param mixed $input
     *
     * @return mixed
     */
    public function make($input)
    {
        if (empty($this->rules)) {
            throw new EmptyRulesException(\sprintf(
                'Decision "%s" cannot be made without any rules',
                \get_class($this)
            ));
        }

        $this->context = $this->createContext($input);

        try {
            $this->createPipeline()->process($this->context);
            $this->doMake($this->context);
        } catch (\Exception $exception) {
            throw new UnableToMakeDecisionException($exception->getMessage(), $exception->getCode(), $exception);
        }

        return $this->context->getInput();
    }

    /**
     * Do make decision based on given context.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\ContextInterface $context
     *
     * @return void
     */
    abstract protected function doMake(ContextInterface $context): void;

    /**
     * Get decision type.
     *
     * @return string
     */
    abstract protected function getDecisionType(): string;

    /**
     * Create context for given input.
     *
     * @param mixed $input
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\ContextInterface
     */
    protected function createContext($input): ContextInterface
    {
        $context = new Context($this->getDecisionType(), $input);

        // If input is an array add context to it
        if (\is_array($input)) {
            // Index context cannot be used by users to avoid unexpected behaviours
            if (isset($input['context'])) {
                throw new ReservedContextIndexException(
                    'When giving an array input to a decision, "context" is a reserved index it cannot be used'
                );
            }

            $input['context'] = $context;
        }

        // Give context to input to be able to stop propagation from expression rules
        if ($input instanceof ContextAwareInterface) {
            $input->setContext($context);
        }

        return $context;
    }

    /**
     * Create middleware list for given rules and class.
     *
     * @param string $class
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\MiddlewareInterface[]
     */
    protected function createMiddlewareList(string $class): array
    {
        $rules = $this->rules;

        \usort($rules, function (RuleInterface $first, RuleInterface $second): bool {
            return $first->getPriority() < $second->getPriority();
        });

        $middlewareList = [];

        foreach ($rules as $rule) {
            $middlewareList[] = new $class($rule);
        }

        return $middlewareList;
    }

    /**
     * Create pipeline.
     *
     * @return \StepTheFkUp\EasyPipeline\Interfaces\PipelineInterface
     */
    private function createPipeline(): PipelineInterface
    {
        return new IlluminatePipeline(
            new BaseIlluminatePipeline(),
            $this->createMiddlewareList($this->getMiddlewareClass())
        );
    }

    /**
     * Get middleware class.
     *
     * @return string
     */
    private function getMiddlewareClass(): string
    {
        if ($this->getDecisionType() === DecisionInterface::TYPE_VALUE) {
            return ValueMiddleware::class;
        }

        return YesNoMiddleware::class;
    }
}
