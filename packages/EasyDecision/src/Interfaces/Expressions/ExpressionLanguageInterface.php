<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces\Expressions;

interface ExpressionLanguageInterface
{
    public function addFunction(ExpressionFunctionInterface $function): self;

    /**
     * @param null|mixed[] $arguments
     *
     * @return mixed
     */
    public function evaluate(string $expression, ?array $arguments = null);

    /**
     * @return \EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface[]
     */
    public function getFunctions(): array;

    /**
     * @param null|string[] $names
     */
    public function validate(string $expression, ?array $names = null): bool;
}
