<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Provider;

interface HandlerConfigProviderInterface
{
    /**
     * @return iterable<\EonX\EasyLogging\Config\HandlerConfigInterface>
     */
    public function handlers(): iterable;
}
