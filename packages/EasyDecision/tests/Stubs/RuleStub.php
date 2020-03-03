<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stubs;

use EonX\EasyDecision\Interfaces\RuleInterface;

final class RuleStub implements RuleInterface
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
     * RuleStub constructor.
     *
     * @param string $name
     * @param mixed $output
     * @param null|bool $supports
     * @param null|int $priority
     */
    public function __construct(string $name, $output, ?bool $supports = null, ?int $priority = null)
    {
        $this->name = $name;
        $this->output = $output;
        $this->supports = $supports ?? true;
        $this->priority = $priority ?? 0;
    }

    /**
     * Get priority.
     *
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Proceed with input.
     *
     * @param mixed[] $input
     *
     * @return mixed
     */
    public function proceed(array $input)
    {
        return $this->output;
    }

    /**
     * Check if rule supports given input.
     *
     * @param mixed[] $input
     *
     * @return bool
     */
    public function supports(array $input): bool
    {
        return $this->supports;
    }

    /**
     * Get string representation of the rule.
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->name;
    }
}
