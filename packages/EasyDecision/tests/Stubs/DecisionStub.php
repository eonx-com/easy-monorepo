<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stubs;

use EonX\EasyDecision\Decisions\AbstractDecision;

final class DecisionStub extends AbstractDecision
{
    /**
     * @var mixed
     */
    private $defaultOutput;

    /**
     * @var mixed
     */
    private $output;

    /**
     * @var null|\Closure
     */
    private $outputClosure;

    public function __construct(
        ?string $name = null,
        $defaultOutput = null,
        $output = null,
        ?\Closure $outputClosure = null
    ) {
        parent::__construct($name);

        $this->outputClosure = $outputClosure;
        $this->defaultOutput = $defaultOutput;
        $this->output = $output;
    }

    protected function doHandleRuleOutput($output): void
    {
        if ($this->outputClosure !== null) {
            \call_user_func($this->outputClosure, $output);
        }
    }

    protected function doMake()
    {
        return $this->output;
    }

    protected function getDefaultOutput()
    {
        return $this->defaultOutput;
    }
}
