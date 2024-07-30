<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Provider;

final readonly class ArrayHandlerConfigProvider implements HandlerConfigProviderInterface
{
    /**
     * @param \EonX\EasyLogging\Config\HandlerConfigInterface[] $handlers
     */
    public function __construct(
        private array $handlers,
    ) {
    }

    /**
     * @return iterable<\EonX\EasyLogging\Config\HandlerConfigInterface>
     */
    public function handlers(): iterable
    {
        foreach ($this->handlers as $handler) {
            yield $handler;
        }
    }
}
