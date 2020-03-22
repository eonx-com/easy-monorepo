<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stubs;

use EonX\EasyDecision\Interfaces\RuleInterface;

class RuleStub implements RuleInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $output;

    /**
     * @var int
     */
    private $priority;

    /**
     * @var bool
     */
    private $supports;

    /**
     * @param mixed $output
     */
    public function __construct(string $name, $output, ?bool $supports = null, ?int $priority = null)
    {
        $this->name = $name;
        $this->output = $output;
        $this->supports = $supports ?? true;
        $this->priority = $priority ?? 0;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param mixed[] $input
     *
     * @return mixed
     */
    public function proceed(array $input)
    {
        return $this->output;
    }

    /**
     * @param mixed[] $input
     */
    public function supports(array $input): bool
    {
        return $this->supports;
    }

    public function toString(): string
    {
        return $this->name;
    }
}
