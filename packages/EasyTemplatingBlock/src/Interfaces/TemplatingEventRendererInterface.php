<?php

declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Interfaces;

interface TemplatingEventRendererInterface
{
    /**
     * @param null|mixed[] $context
     */
    public function renderEvent(string $event, ?array $context = null): string;
}
