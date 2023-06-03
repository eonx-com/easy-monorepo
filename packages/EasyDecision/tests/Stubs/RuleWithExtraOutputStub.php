<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stubs;

use EonX\EasyDecision\Interfaces\DecisionOutputForRuleAwareInterface;

final class RuleWithExtraOutputStub extends RuleStub implements DecisionOutputForRuleAwareInterface
{
    /**
     * @var null|mixed[]
     */
    private $extra;

    /**
     * @param mixed $output
     * @param null|mixed[] $extra
     */
    public function __construct(
        string $name,
        $output,
        ?array $extra = null,
        ?bool $supports = null,
        ?int $priority = null,
    ) {
        $this->extra = $extra;

        parent::__construct($name, $output, $supports, $priority);
    }

    /**
     * @param mixed $decisionOutput
     *
     * @return mixed
     */
    public function getDecisionOutputForRule($decisionOutput)
    {
        if ($this->extra === null) {
            return $decisionOutput;
        }

        $this->extra['output'] = $decisionOutput;

        return $this->extra;
    }
}
