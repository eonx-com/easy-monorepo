<?php

declare(strict_types=1);

namespace EonX\EasyApiPlatform\DataCollector;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use ApiPlatform\Symfony\Bundle\DataCollector\RequestDataCollector as DecoratedRequestDataCollector;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister\TraceableChainSimpleDataPersister;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Throwable;

final class RequestDataCollector extends DataCollector
{
    public function __construct(
        private DecoratedRequestDataCollector $decorated,
        private DataPersisterInterface $dataPersister
    ) {
    }

    public function collect(Request $request, Response $response, ?Throwable $exception = null): void
    {
        $this->decorated->collect($request, $response, $exception);

        // Use Reflection to get data from decorated collector...
        $reflection = new ReflectionProperty($this->decorated::class, 'data');
        $reflection->setAccessible(true);

        $this->data = $reflection->getValue($this->decorated);

        if ($this->dataPersister instanceof TraceableChainSimpleDataPersister) {
            $this->data['dataPersisters'] = ['responses' => $this->dataPersister->getPersistersResponse()];
        }

        $this->data['version'] = $this->decorated->getVersion();
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

    /**
     * @return class-string|null
     */
    public function getResourceClass(): ?string
    {
        return $this->data['resource_class'] ?? null;
    }

    public function getResourceMetadataCollection(): mixed
    {
        return $this->data['resource_metadata_collection'] ?? null;
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
