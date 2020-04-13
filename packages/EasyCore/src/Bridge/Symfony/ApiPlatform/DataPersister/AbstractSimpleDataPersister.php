<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister;

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces\SimpleDataPersisterInterface;

abstract class AbstractSimpleDataPersister implements SimpleDataPersisterInterface
{
    /**
     * @param mixed $data
     */
    public function remove($data): void
    {
        // Not supported by default in simple data persister.
    }

    /**
     * @param mixed $data
     */
    public function supports($data): bool
    {
        $apiResourceClass = $this->getApiResourceClass();

        return $data instanceof $apiResourceClass;
    }
}
