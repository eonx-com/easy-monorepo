<?php

declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Blocks;

use EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockInterface;
use EonX\EasyUtils\Traits\HasPriorityTrait;

abstract class AbstractTemplatingBlock implements TemplatingBlockInterface
{
    use HasPriorityTrait;

    /**
     * @var null|mixed[]
     */
    private ?array $context;

    public function __construct(
        private string $name,
    ) {
    }

    /**
     * @return null|mixed[]
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
     * @param null|mixed[] $context
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
