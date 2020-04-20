<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;

final class TraceableChainSimpleDataPersister implements ContextAwareDataPersisterInterface
{
    /**
     * @var \EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister\ChainSimpleDataPersister
     */
    private $decorated;

    public function __construct(ContextAwareDataPersisterInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * @inheritDoc
     */
    public function supports($data, array $context = []): bool
    {
        return $this->decorated->supports($data, $context);
    }

    /**
     * @inheritDoc
     */
    public function persist($data, array $context = [])
    {
        return $this->decorated->persist($data, $context);
    }

    /**
     * @inheritDoc
     */
    public function remove($data, array $context = [])
    {
        return $this->decorated->remove($data, $context);
    }
}
