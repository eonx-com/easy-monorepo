<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces\DoctrineOrmDataPersisterInterface;

final class DoctrineOrmDataPersister implements DoctrineOrmDataPersisterInterface
{
    /**
     * @var \ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface
     */
    private $decorated;

    public function __construct(ContextAwareDataPersisterInterface $decorated)
    {
        $this->decorated = $decorated;
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

    /**
     * @param mixed $data
     * @param null|mixed[] $context
     */
    public function supports($data, ?array $context = null): bool
    {
        return $this->decorated->supports($data, $context ?? []);
    }
}
