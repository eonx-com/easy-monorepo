<?php

declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Interfaces;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;

interface TemplatingBlockInterface extends HasPriorityInterface
{
    /**
     * @return null|mixed[]
     */
    public function getContext(): ?array;

    public function getName(): string;

    /**
     * @param null|mixed[] $context
     */
    public function setContext(?array $context = null): self;
}
