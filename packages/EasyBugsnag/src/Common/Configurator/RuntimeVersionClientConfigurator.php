<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Common\Configurator;

use Bugsnag\Client;

final class RuntimeVersionClientConfigurator extends AbstractClientConfigurator
{
    public function __construct(
        private readonly string $runtime,
        private readonly string $version,
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
