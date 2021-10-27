<?php

declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Interfaces;

interface TemplatingBlockProviderInterface
{
    /**
     * @param null|mixed[] $context
     *
     * @return iterable<\EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockInterface>
     */
    public function getBlocksForEvent(string $event, ?array $context = null): iterable;
}
