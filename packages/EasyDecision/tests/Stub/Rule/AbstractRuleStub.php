<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stub\Rule;

use EonX\EasyDecision\Rule\RuleInterface;

abstract class AbstractRuleStub implements RuleInterface
{
    private readonly int $priority;

    private readonly bool $supports;

    public function __construct(
        private readonly string $name,
        private readonly mixed $output,
        ?bool $supports = null,
        ?int $priority = null,
    ) {
        $this->supports = $supports ?? true;
        $this->priority = $priority ?? 0;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function proceed(array $input): mixed
    {
        return $this->output;
    }

    public function supports(array $input): bool
    {
        return $this->supports;
    }

    public function toString(): string
    {
        return $this->name;
    }
}
