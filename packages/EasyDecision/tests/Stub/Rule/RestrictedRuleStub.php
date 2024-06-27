<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stub\Rule;

use EonX\EasyDecision\Decision\DecisionInterface;
use EonX\EasyDecision\Rule\RestrictedRuleInterface;

final class RestrictedRuleStub extends RuleStub implements RestrictedRuleInterface
{
    public function __construct(
        string $name,
        private string $supportedDecision,
        mixed $output,
        ?bool $supports = null,
    ) {
        parent::__construct($name, $output, $supports);
    }

    public function supportsDecision(DecisionInterface $decision): bool
    {
        return $decision->getName() === $this->supportedDecision;
    }
}
