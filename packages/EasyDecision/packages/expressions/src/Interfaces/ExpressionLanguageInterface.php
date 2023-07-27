<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Expressions\Interfaces;

use Psr\Cache\CacheItemPoolInterface;

interface ExpressionLanguageInterface
{
    public function addFunction(ExpressionFunctionInterface $function): self;

    /**
     * @param \EonX\EasyDecision\Expressions\Interfaces\ExpressionFunctionInterface[] $functions
     */
    public function addFunctions(array $functions): self;

    public function evaluate(string $expression, ?array $arguments = null): mixed;

    /**
     * @return \EonX\EasyDecision\Expressions\Interfaces\ExpressionFunctionInterface[]
     */
    public function getFunctions(): array;

    public function removeFunction(string $name): self;

    /**
     * @param string[] $names
     */
    public function removeFunctions(array $names): self;

    public function setCache(CacheItemPoolInterface $cache): self;

    /**
     * @param string[]|null $names
     */
    public function validate(string $expression, ?array $names = null): bool;
}
