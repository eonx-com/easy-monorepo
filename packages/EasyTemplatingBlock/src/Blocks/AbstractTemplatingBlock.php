<?php

declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Blocks;

use EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockInterface;
use EonX\EasyUtils\Traits\HasPriorityTrait;

abstract class AbstractTemplatingBlock implements TemplatingBlockInterface
{
    use HasPriorityTrait;

    /**
     * @var mixed[]|null
     */
    private ?array $context = null;

    public function __construct(
        private string $name,
    ) {
    }

    /**
     * @return mixed[]|null
     */
    public function getContext(): ?array
    {
        return $this->context;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param mixed[]|null $context
     */
    public function setContext(?array $context = null): TemplatingBlockInterface
    {
        $this->context = $context;

        return $this;
    }

    public function setPriority(int $priority): TemplatingBlockInterface
    {
        $this->priority = $priority;

        return $this;
    }
}
