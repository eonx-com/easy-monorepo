<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Common\Renderer;

interface TemplatingEventRendererInterface
{
    public function renderEvent(string $event, ?array $context = null): string;
}
