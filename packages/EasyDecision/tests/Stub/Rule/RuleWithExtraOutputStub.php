<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stub\Rule;

use EonX\EasyDecision\Rule\DecisionOutputForRuleAwareInterface;

final class RuleWithExtraOutputStub extends RuleStub implements DecisionOutputForRuleAwareInterface
{
    public function __construct(
        string $name,
        mixed $output,
        private ?array $extra = null,
        ?bool $supports = null,
        ?int $priority = null,
    ) {
        parent::__construct($name, $output, $supports, $priority);
    }

    public function getDecisionOutputForRule(mixed $decisionOutput): mixed
    {
        if ($this->extra === null) {
            return $decisionOutput;
        }

        $this->extra['output'] = $decisionOutput;

        return $this->extra;
    }
}
