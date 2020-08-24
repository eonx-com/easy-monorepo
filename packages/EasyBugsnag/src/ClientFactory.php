<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag;

use Bugsnag\Client;
use Bugsnag\Configuration;
use Bugsnag\Request\ResolverInterface;
use Bugsnag\Shutdown\ShutdownStrategyInterface;
use EonX\EasyBugsnag\Interfaces\ClientConfiguratorInterface;
use EonX\EasyBugsnag\Interfaces\ClientFactoryInterface;
use GuzzleHttp\ClientInterface;

final class ClientFactory implements ClientFactoryInterface
{
    /**
     * @var \EonX\EasyBugsnag\Interfaces\ClientConfiguratorInterface[]
     */
    private $configurators;

    /**
     * @var null|\GuzzleHttp\ClientInterface
     */
    private $httpClient;

    /**
     * @var null|\Bugsnag\Request\ResolverInterface
     */
    private $requestResolver;

    /**
     * @var null|\Bugsnag\Shutdown\ShutdownStrategyInterface
     */
    private $shutdownStrategy;

    public function create(string $apiKey): Client
    {
        $client = new Client(
            new Configuration($apiKey),
            $this->requestResolver,
            $this->httpClient,
            $this->shutdownStrategy
        );

        foreach ($this->configurators as $configurator) {
            $configurator->configure($client);
        }

        return $client;
    }

    /**
     * @param iterable<\EonX\EasyBugsnag\Interfaces\ClientConfiguratorInterface> $configurators
     */
    public function setConfigurators(iterable $configurators): ClientFactoryInterface
    {
        $configurators = $configurators instanceof \Traversable
            ? \iterator_to_array($configurators)
            : (array)$configurators;

        $configurators = \array_filter($configurators, static function ($configurator): bool {
            return $configurator instanceof ClientConfiguratorInterface;
        });

        \usort(
            $configurators,
            static function (ClientConfiguratorInterface $first, ClientConfiguratorInterface $second): int {
                return $first->priority() <=> $second->priority();
            }
        );

        $this->configurators = $configurators;

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
