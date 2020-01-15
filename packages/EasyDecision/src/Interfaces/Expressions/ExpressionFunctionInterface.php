<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces\Expressions;

interface ExpressionFunctionInterface
{
    /**
     * Get description.
     *
     * @return null|string
     */
    public function getDescription(): ?string;

    /**
     * Get callable to evaluate function.
     *
     * @return callable
     */
    public function getEvaluator(): callable;

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string;
}
