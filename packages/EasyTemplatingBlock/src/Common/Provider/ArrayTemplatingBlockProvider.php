<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Common\Provider;

final class ArrayTemplatingBlockProvider implements TemplatingBlockProviderInterface
{
    /**
     * @param \EonX\EasyTemplatingBlock\Common\ValueObject\AbstractTemplatingBlock[][] $blocks
     */
    public function __construct(
        private array $blocks,
    ) {
    }

    /**
     * @return iterable<\EonX\EasyTemplatingBlock\Common\ValueObject\AbstractTemplatingBlock>
     */
    public function getBlocksForEvent(string $event, ?array $context = null): iterable
    {
        foreach ($this->blocks[$event] ?? [] as $block) {
            yield $block;
        }
    }
}
