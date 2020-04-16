<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\Event;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;

final class DataPersisterResolvedEvent
{
    /**
     * @var \ApiPlatform\Core\DataPersister\DataPersisterInterface
     */
    private $dataPersister;

    public function __construct(DataPersisterInterface $dataPersister)
    {
        $this->dataPersister = $dataPersister;
    }

    public function getDataPersister(): DataPersisterInterface
    {
        return $this->dataPersister;
    }
}
