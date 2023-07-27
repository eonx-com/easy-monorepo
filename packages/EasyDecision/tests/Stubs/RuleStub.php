<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stubs;

use EonX\EasyDecision\Interfaces\RuleInterface;

class RuleStub implements RuleInterface
{
    private int $priority;

    private bool $supports;

    public function __construct(
        private string $name,
        private mixed $output,
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
