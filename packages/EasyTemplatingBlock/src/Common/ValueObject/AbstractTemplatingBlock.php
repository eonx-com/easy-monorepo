<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Common\ValueObject;

use EonX\EasyUtils\Common\Helper\HasPriorityInterface;
use EonX\EasyUtils\Common\Helper\HasPriorityTrait;

abstract class AbstractTemplatingBlock implements HasPriorityInterface
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

    public function setContext(?array $context = null): static
    {
        $this->context = $context;

        return $this;
    }

    public function setPriority(int $priority): static
    {
        $this->priority = $priority;

        return $this;
    }
}
