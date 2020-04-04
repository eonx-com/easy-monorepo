<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;

abstract class AbstractContextDataPersister implements ContextAwareDataPersisterInterface
{
    /**
     * @var mixed
     */
    protected $dataPersister;

    /**
     * AbstractContextDataPersister constructor.
     *
     * @param mixed $dataPersister
     */
    public function __construct($dataPersister)
    {
        $this->dataPersister = $dataPersister;
    }

    /**
     * Persist the data.
     *
     * @param mixed $data
     * @param null|mixed[] $context
     *
     * @return object|void
     */
    public function persist($data, ?array $context = null)
    {
        return $this->dataPersister->persist($data, $context);
    }

    /**
     * Removes the data.
     *
     * @param mixed $data
     * @param null|mixed[] $context
     *
     * @return mixed
     */
    public function remove($data, ?array $context = null)
    {
        return $this->dataPersister->remove($data, $context);
    }

    /**
     * Check if supported entity.
     *
     * @param mixed $data
     * @param null|mixed[] $context
     *
     * @return bool
     */
    public function supports($data, ?array $context = null): bool
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
