<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Configurators;

use Bugsnag\Client;

final class RuntimeVersionConfigurator extends AbstractClientConfigurator
{
    /**
     * @var string
     */
    private $runtime;

    /**
     * @var string
     */
    private $version;

    public function __construct(string $runtime, string $version)
    {
        $this->runtime = $runtime;
        $this->version = $version;

        parent::__construct(null);
    }

    public function configure(Client $bugsnag): void
    {
        $bugsnag->getConfig()->mergeDeviceData([
            'runtimeVersions' => [$this->runtime => $this->version],
        ]);
    }
}
