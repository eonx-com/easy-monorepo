<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataCollector;

use ApiPlatform\Core\Bridge\Symfony\Bundle\DataCollector\RequestDataCollector as BaseRequestDataCollector;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister\TraceableChainSimpleDataPersister;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * @deprecated since 4.2.8, will be removed in 5.0.
 */
final class RequestDataCollector extends DataCollector
{
    /**
     * @var TraceableChainSimpleDataPersister|DataPersisterInterface
     */
    private $dataPersister;

    /**
     * @var \ApiPlatform\Core\Bridge\Symfony\Bundle\DataCollector\RequestDataCollector
     */
    private $decorated;

    public function __construct(BaseRequestDataCollector $decorated, DataPersisterInterface $dataPersister)
    {
        $this->decorated = $decorated;
        $this->dataPersister = $dataPersister;
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        $this->decorated->collect($request, $response, $exception);

        // Use Reflection to get data from decorated collector...
        $reflection = new \ReflectionProperty(\get_class($this->decorated), 'data');
        $reflection->setAccessible(true);

        $this->data = $reflection->getValue($this->decorated);

        $this->data['dataPersisters'] = [
            'responses' => $this->dataPersister->getPersistersResponse() ?? [],
        ];

        $this->data['version'] = \method_exists($this->decorated, 'getVersion')
            ? $this->decorated->getVersion()
            : null;
    }

    /**
     * @return mixed[]
     */
    public function getAcceptableContentTypes(): array
    {
        return $this->data['acceptable_content_types'] ?? [];
    }

    /**
     * @return mixed[]
     */
    public function getCollectionDataProviders(): array
    {
        return $this->data['dataProviders']['collection'] ?? [
            'context' => [],
            'responses' => [],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getCounters(): array
    {
        return $this->data['counters'] ?? [];
    }

    /**
     * @return mixed[]
     */
    public function getDataPersisters(): array
    {
        return $this->data['dataPersisters'] ?? [
            'responses' => [],
        ];
    }

    /**
     * @return mixed[]
     */
    public function getFilters(): array
    {
        return $this->data['filters'] ?? [];
    }

    /**
     * @return mixed[]
     */
    public function getItemDataProviders(): array
    {
        return $this->data['dataProviders']['item'] ?? [
            'context' => [],
            'responses' => [],
        ];
    }

    public function getName(): string
    {
        return 'api_platform.data_collector.request';
    }

    /**
     * @return mixed[]
     */
    public function getRequestAttributes(): array
    {
        return $this->data['request_attributes'] ?? [];
    }

    public function getResourceClass(): ?string
    {
        return $this->data['resource_class'] ?? null;
    }

    /**
     * @return null|mixed
     */
    public function getResourceMetadata()
    {
        return $this->data['resource_metadata'] ?? null;
    }

    /**
     * @return mixed[]
     */
    public function getSubresourceDataProviders(): array
    {
        return $this->data['dataProviders']['subresource'] ?? [
            'context' => [],
            'responses' => [],
        ];
    }

    public function getVersion(): ?string
    {
        return $this->data['version'] ?? null;
    }

    public function reset(): void
    {
        $this->data = [];
    }
}
