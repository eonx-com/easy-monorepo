<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Expressions;

use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionFactoryInterface;
use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface;
use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageFactoryInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;

final class ExpressionLanguageFactory implements ExpressionLanguageFactoryInterface
{
    /**
     * @var \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionFactoryInterface
     */
    private $functionFactory;

    /**
     * ExpressionLanguageFactory constructor.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionFactoryInterface $functionFactory
     */
    public function __construct(ExpressionFunctionFactoryInterface $functionFactory)
    {
        $this->functionFactory = $functionFactory;
    }

    /**
     * Create expression language for given config.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface $config
     *
     * @return \StepTheFkUp\EasyDecision\Expressions\ExpressionLanguage
     */
    public function create(ExpressionLanguageConfigInterface $config): ExpressionLanguage
    {
        $expressionLanguage = new ExpressionLanguage(
            $config->getBaseExpressionLanguage() ?? $this->createBaseExpressionLanguage()
        );

        foreach ($config->getFunctions() ?? [] as $function) {
            $expressionLanguage->addFunction($this->functionFactory->create($function));
        }

        foreach ($config->getFunctionProviders() ?? [] as $provider) {
            foreach ($provider->getFunctions() as $function) {
                $expressionLanguage->addFunction($this->functionFactory->create($function));
            }
        }

        return $expressionLanguage;
    }

    /**
     * Create base expression language instance.
     *
     * @return \Symfony\Component\ExpressionLanguage\ExpressionLanguage
     */
    private function createBaseExpressionLanguage(): BaseExpressionLanguage
    {
        return new BaseExpressionLanguage();
    }
}
