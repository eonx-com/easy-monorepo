<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Interfaces\Config;

interface ProcessorConfigProviderInterface
{
    /**
     * @return iterable<\EonX\EasyLogging\Interfaces\Config\ProcessorConfigInterface>
     */
    public function processors(): iterable;
}
