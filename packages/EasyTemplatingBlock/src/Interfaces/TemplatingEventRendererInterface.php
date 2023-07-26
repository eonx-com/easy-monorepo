<?php

declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Interfaces;

interface TemplatingEventRendererInterface
{
    /**
     * @param mixed[]|null $context
     */
    public function renderEvent(string $event, ?array $context = null): string;
}
