<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces\Resolvers;

interface ContextDataResolverInterface
{
    /**
     * Get priority.
     *
     * @return int
     */
    public function getPriority(): int;

    /**
     * Resolve context data.
     *
     * @param \EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface $data
     *
     * @return \EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface
     */
    public function resolve(ContextResolvingDataInterface $data): ContextResolvingDataInterface;
}
