<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Configurators;

use Bugsnag\Client;

final class RuntimeVersionConfigurator extends AbstractClientConfigurator
{
    public function __construct(
        private string $runtime,
        private string $version,
    ) {
        parent::__construct();
    }

    public function configure(Client $bugsnag): void
    {
        $bugsnag->getConfig()
            ->mergeDeviceData([
                'runtimeVersions' => [
                    $this->runtime => $this->version,
                ],
            ]);
    }
}
