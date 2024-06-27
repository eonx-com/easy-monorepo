<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Factory;

use Bugsnag\Client;
use Bugsnag\Configuration;
use Bugsnag\Request\ResolverInterface;
use Bugsnag\Shutdown\ShutdownStrategyInterface;
use EonX\EasyBugsnag\Configurator\ClientConfiguratorInterface;
use EonX\EasyUtils\Common\Helper\CollectorHelper;
use GuzzleHttp\ClientInterface;

final class ClientFactory implements ClientFactoryInterface
{
    /**
     * @var \EonX\EasyBugsnag\Configurator\ClientConfiguratorInterface[]
     */
    private array $configurators;

    private ?ClientInterface $httpClient = null;

    private ?ResolverInterface $requestResolver = null;

    private ?ShutdownStrategyInterface $shutdownStrategy = null;

    public function create(string $apiKey): Client
    {
        $client = new Client(
            new Configuration($apiKey),
            $this->requestResolver,
            $this->httpClient,
            $this->shutdownStrategy
        );

        $client->registerDefaultCallbacks();

        foreach ($this->configurators as $configurator) {
            $configurator->configure($client);
        }

        return $client;
    }

    /**
     * @param iterable<\EonX\EasyBugsnag\Configurator\ClientConfiguratorInterface> $configurators
     */
    public function setConfigurators(iterable $configurators): ClientFactoryInterface
    {
        $this->configurators = CollectorHelper::orderLowerPriorityFirstAsArray(
            CollectorHelper::filterByClass($configurators, ClientConfiguratorInterface::class)
        );

        return $this;
    }

    public function setHttpClient(?ClientInterface $httpClient = null): ClientFactoryInterface
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    public function setRequestResolver(?ResolverInterface $requestResolver = null): ClientFactoryInterface
    {
        $this->requestResolver = $requestResolver;

        return $this;
    }

    public function setShutdownStrategy(?ShutdownStrategyInterface $shutdownStrategy = null): ClientFactoryInterface
    {
        $this->shutdownStrategy = $shutdownStrategy;

        return $this;
    }
}
