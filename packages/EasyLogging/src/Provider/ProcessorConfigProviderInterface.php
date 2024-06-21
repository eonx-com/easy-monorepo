<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Provider;

interface ProcessorConfigProviderInterface
{
    /**
     * @return iterable<\EonX\EasyLogging\Config\ProcessorConfigInterface>
     */
    public function processors(): iterable;
}
