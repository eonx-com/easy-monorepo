<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Symfony\AppStateResetters;

use function Symfony\Component\String\u;

final class SymfonyServicesAppStateResetter extends AbstractSymfonyServicesAppStateResetter
{
    protected function shouldReset(string $service): bool
    {
        return u($service)->containsAny('cache') === false;
    }
}
