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

    public function __construct(BaseExpressionLanguage $expressionLanguage)
    {
        $this->expressionLanguage = $expressionLanguage;
    }

    public function addFunction(ExpressionFunctionInterface $function): ExpressionLanguageInterface
    {
        $this->expressionLanguage->register($function->getName(), $this->getEmptyCallable(), $function->getEvaluator());
        $this->functions[] = $function;

        return $this;
    }

    /**
     * @param null|mixed[] $arguments
     *
     * @return mixed
     */
    public function evaluate(string $expression, ?array $arguments = null)
    {
        return $this->expressionLanguage->evaluate($expression, $arguments ?? []);
    }

    /**
     * @return \EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface[]
     */
    public function getFunctions(): array
    {
        return $this->functions;
    }

    /**
     * @param null|string[] $names
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

    private function getEmptyCallable(): callable
    {
        return function (): void {
        };
    }
}
