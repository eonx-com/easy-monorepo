<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Data;

final class DecisionDataFlow
{
    /**
     * @var null|mixed
     */
    private $defaultOutput;

    /**
     * @var mixed[]
     */
    private $originalInput;

    /**
     * @var \EonX\EasyDecision\Interfaces\RuleInterface[]
     */
    private $originalRules;

    /**
     * @var \EonX\EasyDecision\Interfaces\RuleInterface[]
     */
    private $processedRules;

    /**
     * @var mixed[]
     */
    private $ruleOutputs;

    public function __construct(
        array $originalInput,
        array $originalRules,
        array $processedRules,
        $defaultOutput,
        array $ruleOutputs
    ) {
        $this->originalInput = $originalInput;
        $this->originalRules = $originalRules;
        $this->processedRules = $processedRules;
        $this->defaultOutput = $defaultOutput;
        $this->ruleOutputs = $ruleOutputs;
    }

    /**
     * @return null|mixed
     */
    public function getDefaultOutput()
    {
        return $this->defaultOutput;
    }

    /**
     * @return mixed[]
     */
    public function getOriginalInput(): array
    {
        return $this->originalInput;
    }

    /**
     * @return \EonX\EasyDecision\Interfaces\RuleInterface[]
     */
    public function getOriginalRules(): array
    {
        return $this->originalRules;
    }

    /**
     * @return \EonX\EasyDecision\Interfaces\RuleInterface[]
     */
    public function getProcessedRules(): array
    {
        return $this->processedRules;
    }

    /**
     * @return mixed[]
     */
    public function getRuleOutputs(): array
    {
        return $this->ruleOutputs;
    }
}
