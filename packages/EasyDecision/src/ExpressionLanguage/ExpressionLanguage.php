<?php
declare(strict_types=1);

namespace EonX\EasyDecision\ExpressionLanguage;

use EonX\EasyDecision\Exception\ExpressionLanguageLockedException;
use EonX\EasyDecision\Exception\InvalidExpressionException;
use EonX\EasyDecision\ExpressionFunction\ExpressionFunctionInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

final class ExpressionLanguage implements ExpressionLanguageInterface
{
    private ?CacheItemPoolInterface $cache = null;

    private BaseExpressionLanguage $expressionLanguage;

    /**
     * @var \EonX\EasyDecision\ExpressionFunction\ExpressionFunctionInterface[]
     */
    private array $functions = [];

    private bool $locked = false;

    public function addFunction(ExpressionFunctionInterface $function): ExpressionLanguageInterface
    {
        $this->isLocked(__METHOD__);

        $this->doAddFunctions([$function]);

        return $this;
    }

    /**
     * @param \EonX\EasyDecision\ExpressionFunction\ExpressionFunctionInterface[] $functions
     */
    public function addFunctions(array $functions): ExpressionLanguageInterface
    {
        $this->isLocked(__METHOD__);

        $this->doAddFunctions($functions);

        return $this;
    }

    public function evaluate(string $expression, ?array $arguments = null): mixed
    {
        return $this->getExpressionLanguage()
            ->evaluate($expression, $arguments ?? []);
    }

    /**
     * @return \EonX\EasyDecision\ExpressionFunction\ExpressionFunctionInterface[]
     */
    public function getFunctions(): array
    {
        return \array_values($this->functions);
    }

    public function removeFunction(string $name): ExpressionLanguageInterface
    {
        $this->isLocked(__METHOD__);

        $this->doRemoveFunctions([$name]);

        return $this;
    }

    /**
     * @param string[] $names
     */
    public function removeFunctions(array $names): ExpressionLanguageInterface
    {
        $this->isLocked(__METHOD__);

        $this->doRemoveFunctions($names);

        return $this;
    }

    public function setCache(CacheItemPoolInterface $cache): ExpressionLanguageInterface
    {
        $this->isLocked(__METHOD__);

        $this->cache = $cache;

        return $this;
    }

    /**
     * @param string[]|null $names
     *
     * @throws \EonX\EasyDecision\Exception\InvalidExpressionException
     */
    public function validate(string $expression, ?array $names = null): bool
    {
        try {
            $this->getExpressionLanguage()
                ->parse($expression, $names ?? []);

            return true;
        } catch (SyntaxError $exception) {
            throw new InvalidExpressionException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @param \EonX\EasyDecision\ExpressionFunction\ExpressionFunctionInterface[] $functions
     */
    private function doAddFunctions(array $functions): void
    {
        foreach (CollectorHelper::filterByClass($functions, ExpressionFunctionInterface::class) as $function) {
            $this->functions[$function->getName()] = $function;
        }
    }

    /**
     * @param string[] $names
     */
    private function doRemoveFunctions(array $names): void
    {
        foreach ($names as $name) {
            unset($this->functions[$name]);
        }
    }

    private function getExpressionLanguage(): BaseExpressionLanguage
    {
        if ($this->locked) {
            return $this->expressionLanguage;
        }

        $this->locked = true;

        // Legacy to be removed in 3.0
        $expressionLanguage = new BaseExpressionLanguage($this->cache);

        foreach ($this->functions as $function) {
            $expressionLanguage->register(
                $function->getName(),
                static function (): void {
                },
                $function->getEvaluator()
            );
        }

        return $this->expressionLanguage = $expressionLanguage;
    }

    private function isLocked(string $method): void
    {
        if ($this->locked) {
            throw new ExpressionLanguageLockedException(\sprintf(
                'Cannot call "%s" when expression language locked',
                $method
            ));
        }
    }
}
