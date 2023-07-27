<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Providers;

use EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockProviderInterface;

final class ArrayTemplatingBlockProvider implements TemplatingBlockProviderInterface
{
    /**
     * @param \EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockInterface[][] $blocks
     */
    public function __construct(
        private array $blocks,
    ) {
    }

    /**
     * @return iterable<\EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockInterface>
     */
    public function getBlocksForEvent(string $event, ?array $context = null): iterable
    {
        foreach ($this->blocks[$event] ?? [] as $block) {
            yield $block;
        }
    }
}
