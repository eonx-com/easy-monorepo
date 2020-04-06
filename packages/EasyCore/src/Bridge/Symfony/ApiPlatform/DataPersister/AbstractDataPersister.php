<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;

abstract class AbstractDataPersister implements DataPersisterInterface
{
    /**
     * @var \ApiPlatform\Core\DataPersister\DataPersisterInterface
     */
    private $decorated;

    public function __construct(DataPersisterInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * @param mixed $data
     *
     * @return object|void
     */
    public function persist($data)
    {
        return $this->decorated->persist($data);
    }

    /**
     * @param mixed $data
     *
     * @return mixed
     */
    public function remove($data)
    {
        return $this->decorated->remove($data);
    }

    /**
     * @param mixed $data
     *
     * @return bool
     */
    public function supports($data): bool
    {
        $entity = $this->getApiResourceClass();

        return $data instanceof $entity;
    }

    abstract protected function getApiResourceClass(): string;
}
