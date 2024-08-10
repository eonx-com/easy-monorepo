<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Common\Provider;

interface TemplatingBlockProviderInterface
{
    /**
     * @return iterable<\EonX\EasyTemplatingBlock\Common\ValueObject\AbstractTemplatingBlock>
     */
    public function getBlocksForEvent(string $event, ?array $context = null): iterable;
}
