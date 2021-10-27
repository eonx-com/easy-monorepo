<?php

declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Providers;

use EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockProviderInterface;

final class ArrayTemplatingBlockProvider implements TemplatingBlockProviderInterface
{
    /**
     * @var \EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockInterface[][]
     */
    private $blocks;

    /**
     * @param \EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockInterface[][] $blocks
     */
    public function __construct(array $blocks)
    {
        $this->blocks = $blocks;
    }

    /**
     * @param null|mixed[] $context
     *
     * @return iterable<\EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockInterface>
     */
    public function getBlocksForEvent(string $event, ?array $context = null): iterable
    {
        foreach ($this->blocks[$event] ?? [] as $block) {
            yield $block;
        }
    }
}
