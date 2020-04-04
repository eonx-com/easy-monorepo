<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;

abstract class AbstractDataPersister implements DataPersisterInterface
{
    /**
     * @var mixed
     */
    protected $dataPersister;

    /**
     * AbstractDataPersister constructor.
     *
     * @param mixed $dataPersister
     */
    public function __construct($dataPersister)
    {
        $this->dataPersister = $dataPersister;
    }

    /**
     * Persists the data.
     *
     * @param mixed $data
     *
     * @return object|void
     */
    public function persist($data)
    {
        return $this->dataPersister->persist($data);
    }

    /**
     * Removes the data.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function remove($data)
    {
        return $this->dataPersister->remove($data);
    }

    /**
     * Is the data supported by the persister?
     *
     * @param mixed $data
     *
     * @return bool
     */
    public function supports($data): bool
    {
        $entity = $this->getApiResourceClass();

        return $data instanceof $entity;
    }

    /**
     * Returns entity class name.
     *
     * @return string
     */
    abstract protected function getApiResourceClass(): string;
}
