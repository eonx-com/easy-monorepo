<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Interfaces;

use EonX\EasyUtils\Common\Helper\HasPriorityInterface;

interface TemplatingBlockInterface extends HasPriorityInterface
{
    public function getContext(): ?array;

    public function getName(): string;

    public function setContext(?array $context = null): self;
}
