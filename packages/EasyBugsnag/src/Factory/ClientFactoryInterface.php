<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Factory;

use Bugsnag\Client;
use Bugsnag\Request\ResolverInterface;
use Bugsnag\Shutdown\ShutdownStrategyInterface;
use GuzzleHttp\ClientInterface;

interface ClientFactoryInterface
{
    public function create(string $apiKey): Client;

    /**
     * @param iterable<\EonX\EasyBugsnag\Configurator\ClientConfiguratorInterface> $configurators
     */
    public function setConfigurators(iterable $configurators): self;

    public function setHttpClient(?ClientInterface $httpClient = null): self;

    public function setRequestResolver(?ResolverInterface $requestResolver = null): self;

    public function setShutdownStrategy(?ShutdownStrategyInterface $shutdownStrategy = null): self;
}
