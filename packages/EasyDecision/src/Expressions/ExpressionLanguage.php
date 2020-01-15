<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Expressions;

use EonX\EasyDecision\Exceptions\InvalidExpressionException;
use EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface;
use EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

final class ExpressionLanguage implements ExpressionLanguageInterface
{
    /**
     * @var \Symfony\Component\ExpressionLanguage\ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * @var \EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface[]
     */
    private $functions = [];

    /**
     * ExpressionLanguage constructor.
     *
     * @param \Symfony\Component\ExpressionLanguage\ExpressionLanguage $expressionLanguage
     */
    public function __construct(BaseExpressionLanguage $expressionLanguage)
    {
        $this->expressionLanguage = $expressionLanguage;
    }

    /**
     * Add function to use in expressions.
     *
     * @param \EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface $function
     *
     * @return \EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface
     */
    public function addFunction(ExpressionFunctionInterface $function): ExpressionLanguageInterface
    {
        $this->expressionLanguage->register($function->getName(), $this->getEmptyCallable(), $function->getEvaluator());
        $this->functions[] = $function;

        return $this;
    }

    /**
     * Evaluate given expression with given arguments and return output.
     *
     * @param string $expression
     * @param null|mixed[] $arguments
     *
     * @return mixed
     */
    public function evaluate(string $expression, ?array $arguments = null)
    {
        return $this->expressionLanguage->evaluate($expression, $arguments ?? []);
    }

    /**
     * Get list of functions added.
     *
     * @return \EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface[]
     */
    public function getFunctions(): array
    {
        return $this->functions;
    }

    /**
     * Validate given expression for given names.
     *
     * @param string $expression
     * @param null|string[] $names
     *
     * @return bool
     *
     * @throws \EonX\EasyDecision\Exceptions\InvalidExpressionException
     */
    public function validate(string $expression, ?array $names = null): bool
    {
        try {
            $this->expressionLanguage->parse($expression, $names ?? []);

            return true;
        } catch (SyntaxError $exception) {
            throw new InvalidExpressionException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Get empty callable for compiler.
     *
     * @return callable
     */
    private function getEmptyCallable(): callable
    {
        return function (): void {
        };
    }
}
