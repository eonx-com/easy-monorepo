<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataCollector;

use ApiPlatform\Core\Bridge\Symfony\Bundle\DataCollector\RequestDataCollector as BaseRequestDataCollector;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

final class RequestDataCollector extends DataCollector
{
    /**
     * @var \EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister\TraceableChainSimpleDataPersister
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

    public function __call(string $name, array $arguments)
    {
        return $this->decorated->{$name}(...$arguments);
    }

    public function collect(Request $request, Response $response, \Throwable $exception = null): void
    {
        $this->decorated->collect($request, $response, $exception);

        $this->data['dataPersisters'] = $this->dataPersister->getPersistersResponse();
    }

    public function getDataPersisters(): array
    {
        return $this->data['dataPersisters'] ?? ['responses' => []];
    }

    public function getName(): string
    {
        return $this->decorated->getName();
    }

    public function reset(): void
    {
        $this->decorated->reset();
    }
}
