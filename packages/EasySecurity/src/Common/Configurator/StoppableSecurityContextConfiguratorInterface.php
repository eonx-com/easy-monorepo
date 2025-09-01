<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Common\Configurator;

interface StoppableSecurityContextConfiguratorInterface
{
    public function isPropagationStopped(): bool;

    public function stopPropagation(): void;
}
