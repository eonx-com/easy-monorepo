<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Common\ValueObject;

use EonX\EasyUtils\Traits\HasPriorityTrait;

abstract class AbstractTemplatingBlock implements TemplatingBlockInterface
{
    use HasPriorityTrait;

    private ?array $context = null;

    public function __construct(
        private string $name,
    ) {
    }

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function getName(): string
    {
        return $this->name;
    }

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
