<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;

abstract class AbstractContextDataPersister implements ContextAwareDataPersisterInterface
{
    /**
     * @var mixed
     */
    protected $decorated;

    public function __construct(ContextAwareDataPersisterInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * @param mixed $data
     * @param null|mixed[] $context
     *
     * @return object
     */
    public function persist($data, ?array $context = null)
    {
        return $this->decorated->persist($data, $context ?? []);
    }

    /**
     * @param mixed $data
     * @param null|mixed[] $context
     *
     * @return object
     */
    public function remove($data, ?array $context = null)
    {
        return $this->decorated->remove($data, $context ?? []);
    }

    /**
     * @param mixed $data
     * @param null|mixed[] $context
     */
    public function supports($data, ?array $context = null): bool
    {
        $entity = $this->getApiResourceClass();

        return $data instanceof $entity;
    }

    abstract protected function getApiResourceClass(): string;
}
