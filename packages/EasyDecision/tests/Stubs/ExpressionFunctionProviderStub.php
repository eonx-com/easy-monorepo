<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Tests\Stubs;

use LoyaltyCorp\EasyDecision\Expressions\ExpressionFunctionFactory;
use LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionFunctionProviderInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction as BaseExpressionFunction;

final class ExpressionFunctionProviderStub implements ExpressionFunctionProviderInterface
{
    /**
     * @var \LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionFunctionFactoryInterface
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
     * @return \LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface[]
     */
    public function getFunctions(): array
    {
        return [
            $this->functionFactory->create(BaseExpressionFunction::fromPhp('max')),
            $this->functionFactory->create(BaseExpressionFunction::fromPhp('min'))
        ];
    }
}


