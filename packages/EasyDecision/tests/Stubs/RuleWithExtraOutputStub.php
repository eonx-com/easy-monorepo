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
     * RuleWithExtraOutputStub constructor.
     *
     * @param string $name
     * @param $output
     * @param null|mixed[] $extra
     * @param null|bool $supports
     * @param null|int $priority
     */
    public function __construct(
        string $name,
        $output,
        ?array $extra = null,
        ?bool $supports = null,
        ?int $priority = null
    ) {
        $this->extra = $extra;

        parent::__construct($name, $output, $supports, $priority);
    }

    /**
     * Returns rule output for given decision output.
     *
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
