<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stubs;

use EonX\EasyDecision\Expressions\ExpressionFunctionFactory;
use EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionProviderInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction as BaseExpressionFunction;

final class ExpressionFunctionProviderStub implements ExpressionFunctionProviderInterface
{
    /**
     * @var \EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionFactoryInterface
     */
    private $functionFactory;

    public function __construct()
    {
        $this->functionFactory = new ExpressionFunctionFactory();
    }

    /**
     * @return \EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface[]
     */
    public function getFunctions(): array
    {
        return [
            $this->functionFactory->create(BaseExpressionFunction::fromPhp('max')),
            $this->functionFactory->create(BaseExpressionFunction::fromPhp('min'))
        ];
    }
}
