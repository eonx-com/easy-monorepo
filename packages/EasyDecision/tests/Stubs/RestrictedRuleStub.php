<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stubs;

use EonX\EasyDecision\Interfaces\DecisionInterface;
use EonX\EasyDecision\Interfaces\RestrictedRuleInterface;

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
