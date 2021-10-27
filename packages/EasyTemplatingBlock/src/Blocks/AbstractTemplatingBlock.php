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
    private $context;

    /**
     * @var string
     */
    private $name;

    public function __construct(string $name, ?int $priority = null)
    {
        $this->name = $name;

        if ($priority !== null) {
            $this->priority = $priority;
        }
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
}
