<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;

interface SimpleDataPersisterInterface extends DataPersisterInterface
{
    public function getApiResourceClass(): string;
}
