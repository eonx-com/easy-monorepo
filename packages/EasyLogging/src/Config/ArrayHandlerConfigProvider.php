<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Config;

use EonX\EasyLogging\Interfaces\Config\HandlerConfigProviderInterface;

final class ArrayHandlerConfigProvider implements HandlerConfigProviderInterface
{
    /**
     * @param \EonX\EasyLogging\Interfaces\Config\HandlerConfigInterface[] $handlers
     */
    public function __construct(
        private array $handlers,
    ) {
    }

    /**
     * @return iterable<\EonX\EasyLogging\Interfaces\Config\HandlerConfigInterface>
     */
    public function handlers(): iterable
    {
        foreach ($this->handlers as $handler) {
            yield $handler;
        }
    }
}
