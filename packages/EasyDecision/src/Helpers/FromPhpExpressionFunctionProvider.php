<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Helpers;

use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionProviderInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

final class FromPhpExpressionFunctionProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @var string[]
     */
    private $phpFunctions;

    /**
     * FromPhpExpressionFunctionProvider constructor.
     *
     * @param string[] $phpFunctions
     */
    public function __construct(array $phpFunctions)
    {
        $this->phpFunctions = $phpFunctions;
    }

    /**
     * Get list of functions.
     *
     * @return mixed[]
     */
    public function getFunctions(): array
    {
        $expressionFunctions = [];

        foreach ($this->phpFunctions as $phpFunction) {
            $expressionFunctions[] = ExpressionFunction::fromPhp($phpFunction);
        }

        return $expressionFunctions;
    }
}

\class_alias(
    FromPhpExpressionFunctionProvider::class,
    'LoyaltyCorp\EasyDecision\Helpers\FromPhpExpressionFunctionProvider',
    false
);
