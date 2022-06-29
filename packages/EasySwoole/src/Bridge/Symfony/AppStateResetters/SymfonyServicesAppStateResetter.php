<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Symfony\AppStateResetters;

use EonX\EasySwoole\AppStateResetters\AbstractAppStateResetter;
use Symfony\Contracts\Service\ResetInterface;

final class SymfonyServicesAppStateResetter extends AbstractAppStateResetter
{
    public function __construct(
        private readonly ResetInterface $servicesResetter,
        ?int $priority = null
    ) {
        parent::__construct($priority);
    }

    public function resetState(): void
    {
        $this->servicesResetter->reset();
    }
}
