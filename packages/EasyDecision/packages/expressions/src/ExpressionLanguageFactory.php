<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Expressions;

use EonX\EasyDecision\Expressions\Interfaces\ExpressionFunctionFactoryInterface;
use EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageConfigInterface;
use EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageFactoryInterface;
use EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageInterface;
use EonX\EasyDecision\Interfaces\DecisionConfiguratorInterface;

final class ExpressionLanguageFactory implements ExpressionLanguageFactoryInterface
{
    /**
     * @var null|\EonX\EasyDecision\Expressions\Interfaces\ExpressionFunctionFactoryInterface
     */
    private $functionFactory;

    public function __construct(?ExpressionFunctionFactoryInterface $functionFactory = null)
    {
        if ($functionFactory !== null) {
            @\trigger_error(\sprintf(
                'Passing %s in %s constructor is deprecated since 2.3.7 and will be removed in 3.0. Use a %s instead',
                ExpressionFunctionFactoryInterface::class,
                static::class,
                DecisionConfiguratorInterface::class
            ), \E_USER_DEPRECATED);
        }

        $this->functionFactory = $functionFactory;
    }

    public function create(?ExpressionLanguageConfigInterface $config = null): ExpressionLanguageInterface
    {
        if ($config === null) {
            return new ExpressionLanguage();
        }

        @\trigger_error(\sprintf(
            'Passing %s in %s::create() is deprecated since 2.3.7 and will be removed in 3.0. Use a %s instead',
            ExpressionLanguageConfigInterface::class,
            static::class,
            DecisionConfiguratorInterface::class
        ), \E_USER_DEPRECATED);

        $expressionLanguage = new ExpressionLanguage($config->getBaseExpressionLanguage());

        if ($this->functionFactory !== null) {
            foreach ($config->getFunctions() ?? [] as $function) {
                $expressionLanguage->addFunction($this->functionFactory->create($function));
            }

            foreach ($config->getFunctionProviders() ?? [] as $provider) {
                foreach ($provider->getFunctions() as $function) {
                    $expressionLanguage->addFunction($this->functionFactory->create($function));
                }
            }
        }

        return $expressionLanguage;
    }
}
