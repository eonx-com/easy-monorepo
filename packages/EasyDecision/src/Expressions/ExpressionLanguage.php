<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Expressions;

use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionProviderInterface;
use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface;
use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;

final class ExpressionLanguage implements ExpressionLanguageInterface
{
    /**
     * @var \Symfony\Component\ExpressionLanguage\ExpressionLanguage
     */
    private $expressionLanguage;

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
     * @param \StepTheFkUp\EasyDecision\Interfaces\ExpressionFunctionInterface $function
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\Expression\ExpressionLanguageInterface
     */
    public function addFunction(ExpressionFunctionInterface $function): ExpressionLanguageInterface
    {
        $this->expressionLanguage->register($function->getName(), $this->getEmptyCallable(), $function->getEvaluator());

        return $this;
    }

    /**
     * Add function provider to add multiple functions at once.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\Expression\ExpressionFunctionProviderInterface $provider
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\Expression\ExpressionLanguageInterface
     */
    public function addFunctionProvider(ExpressionFunctionProviderInterface $provider): ExpressionLanguageInterface
    {
        foreach ($provider->getFunctions() as $function) {
            $this->addFunction($function);
        }

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
     * Get empty callable for compiler.
     *
     * @return callable
     */
    private function getEmptyCallable(): callable
    {
        return function () {
        };
    }
}
