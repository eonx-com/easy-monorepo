<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Helpers;

use EonX\EasyDecision\Expressions\ExpressionFunction;
use EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionProviderInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction as BaseExpressionFunction;

final class FromPhpExpressionFunctionProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @var string[]
     */
    private $phpFunctions;

    /**
     * @param string[] $phpFunctions
     */
    public function __construct(array $phpFunctions)
    {
        $this->phpFunctions = $phpFunctions;
    }

    /**
     * @return mixed[]
     */
    public function getFunctions(): array
    {
        $expressionFunctions = [];

        foreach ($this->phpFunctions as $phpFunction) {
            $phpFunction = (array)$phpFunction;

            // ['function' => 'min', 'description' => 'Optional text']
            // or
            // ['fn' => 'min', 'description' => 'Optional text']
            /** @var null|string $fn */
            $fn = $phpFunction['function'] ?? $phpFunction['fn'] ?? null;
            if (\is_string($fn)) {
                $base = BaseExpressionFunction::fromPhp($phpFunction['function'] ?? $phpFunction['fn']);
                $description = $phpFunction['description'] ?? null;

                $expressionFunctions[] = new ExpressionFunction($base->getName(), $base->getEvaluator(), $description);
                continue;
            }

            // [0 => 'min', 1 => 'Optional text']
            if (\is_string($phpFunction[0] ?? null)) {
                $base = BaseExpressionFunction::fromPhp($phpFunction[0]);
                $description = $phpFunction[1] ?? null;

                $expressionFunctions[] = new ExpressionFunction($base->getName(), $base->getEvaluator(), $description);
                continue;
            }
        }

        return $expressionFunctions;
    }
}
