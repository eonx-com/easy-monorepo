<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Expressions;

use EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface;

final class ExpressionFunction implements ExpressionFunctionInterface
{
    /**
     * @var null|string
     */
    private $description;

    /**
     * @var callable
     */
    private $evaluator;

    /**
     * @var string
     */
    private $name;

    /**
     * ExpressionFunction constructor.
     *
     * @param string $name
     * @param callable $evaluator
     * @param null|string $description
     */
    public function __construct(string $name, callable $evaluator, ?string $description = null)
    {
        $this->name = $name;
        $this->evaluator = $evaluator;
        $this->description = $description;
    }

    /**
     * Get description.
     *
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Get callable to evaluate function.
     *
     * @return callable
     */
    public function getEvaluator(): callable
    {
        return $this->evaluator;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}


