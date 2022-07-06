<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use Symfony\Contracts\Service\ResetInterface;

final class TraceableChainSimpleDataPersister implements ContextAwareDataPersisterInterface, ResetInterface
{
    /**
     * @var \ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface
     */
    private $decorated;

    /**
     * @var mixed[]
     */
    private $persisterResponse = [];

    public function __construct(ContextAwareDataPersisterInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * @return mixed[]
     */
    public function getPersistersResponse(): array
    {
        return $this->persisterResponse;
    }

    /**
     * @param mixed $data
     * @param null|mixed[] $context
     *
     * @return mixed
     */
    public function persist($data, ?array $context = null)
    {
        return $this->decorated->persist($data, $context ?? []);
    }

    /**
     * @param mixed $data
     * @param null|mixed[] $context
     */
    public function remove($data, ?array $context = null): void
    {
        $this->decorated->remove($data, $context ?? []);
    }

    public function reset(): void
    {
        $this->persisterResponse = [];

        if ($this->decorated instanceof ResetInterface) {
            $this->decorated->reset();
        }
    }

    /**
     * @param mixed $data
     * @param null|mixed[] $context
     */
    public function supports($data, ?array $context = null): bool
    {
        $this->resolvePersistersResponse($data, $context);

        return $this->decorated->supports($data, $context ?? []);
    }

    /**
     * @param mixed $data
     * @param null|mixed[] $context
     */
    private function resolvePersistersResponse($data, ?array $context = null): void
    {
        $match = null;

        foreach ($this->decorated->getSimpleDataPersisters() as $apiResource => $persister) {
            if ($match === null && \is_object($data) && $data instanceof $apiResource) {
                $this->persisterResponse[$persister] = $match = true;

                continue;
            }

            $this->persisterResponse[$persister] = $match ? null : false;
        }

        foreach ($this->decorated->getDataPersisters() as $persister) {
            /** @var \ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface $persister */
            $class = \get_class($persister);

            if (isset($this->persisterResponse[$class])) {
                continue;
            }

            if ($match === null && $persister->supports($data, $context ?? [])) {
                $this->persisterResponse[$class] = $match = true;

                continue;
            }

            $this->persisterResponse[$class] = $match ? null : false;
        }
    }
}
