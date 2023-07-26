<?php

declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Interfaces;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;

interface TemplatingBlockInterface extends HasPriorityInterface
{
    /**
     * @return mixed[]|null
     */
    public function getContext(): ?array;

    public function getName(): string;

    /**
     * @param mixed[]|null $context
     */
    public function setContext(?array $context = null): self;
}
