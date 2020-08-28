<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Interfaces;

use Bugsnag\Client;

interface ClientConfiguratorInterface
{
    public function configure(Client $bugsnag): void;

    public function priority(): int;
}
