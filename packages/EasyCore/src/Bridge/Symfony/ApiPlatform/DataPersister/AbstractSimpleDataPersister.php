<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister;

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces\SimpleDataPersisterInterface;

abstract class AbstractSimpleDataPersister implements SimpleDataPersisterInterface
{
    /**
     * @param mixed $data
     * @param null|mixed[] $context
     */
    public function remove($data, ?array $context = null): void
    {
        // Not supported by default in simple data persister.
    }

    /**
     * @param mixed $data
     * @param null|mixed[] $context
     */
    public function supports($data, ?array $context = null): bool
    {
        $apiResourceClass = $this->getApiResourceClass();

        return $data instanceof $apiResourceClass;
    }
}
