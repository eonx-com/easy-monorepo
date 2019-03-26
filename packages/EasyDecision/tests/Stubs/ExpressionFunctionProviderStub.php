<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Tests\Stubs;

use StepTheFkUp\EasyDecision\Expressions\ExpressionFunctionFactory;
use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionProviderInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction as BaseExpressionFunction;

final class ExpressionFunctionProviderStub implements ExpressionFunctionProviderInterface
{
    /**
     * @var \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionFactoryInterface
     */
    private $functionFactory;

    /**
     * ExpressionFunctionProviderStub constructor.
     */
    public function __construct()
    {
        $this->functionFactory = new ExpressionFunctionFactory();
    }

    /**
     * Get list of functions.
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface[]
     */
    public function getFunctions(): array
    {
        return [
            $this->functionFactory->create(BaseExpressionFunction::fromPhp('max')),
            $this->functionFactory->create(BaseExpressionFunction::fromPhp('min'))
        ];
    }
}
