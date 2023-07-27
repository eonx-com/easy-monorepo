<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Interfaces\Config;

interface HandlerConfigProviderInterface
{
    /**
     * @return iterable<\EonX\EasyLogging\Interfaces\Config\HandlerConfigInterface>
     */
    public function handlers(): iterable;
}
