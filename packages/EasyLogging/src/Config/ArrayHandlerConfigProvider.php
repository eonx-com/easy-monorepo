<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Config;

use EonX\EasyLogging\Interfaces\Config\HandlerConfigProviderInterface;

final class ArrayHandlerConfigProvider implements HandlerConfigProviderInterface
{
    /**
     * @var \EonX\EasyLogging\Interfaces\Config\HandlerConfigInterface[]
     */
    private $handlers;

    /**
     * @param \EonX\EasyLogging\Interfaces\Config\HandlerConfigInterface[] $handlers
     */
    public function __construct(array $handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * @return iterable<\EonX\EasyLogging\Interfaces\Config\HandlerConfigInterface>
     */
    public function handlers(): iterable
    {
        return $this->handlers;
    }
}
