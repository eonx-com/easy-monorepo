<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister;

use ApiPlatform\Core\Util\ClassInfoTrait;
use Doctrine\Persistence\ObjectManager as DoctrineObjectManager;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces\DoctrineOrmDataPersisterInterface;
use Doctrine\Persistence\ManagerRegistry;

final class DoctrineOrmDataPersister implements DoctrineOrmDataPersisterInterface
{
    use ClassInfoTrait;

    /**
     * @var \Doctrine\Persistence\ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param object $data
     */
    public function supports($data, ?array $context = null): bool
    {
        return null !== $this->getManager($data);
    }

    /**
     * @param object $data
     *
     * @return object
     */
    public function persist($data, ?array $context = null)
    {
        $manager = $this->getManager($data);

        if ($manager === null) {
            return $data;
        }

        if ($manager->contains($data) === false) {
            $manager->persist($data);
        }

        $manager->flush();

        return $data;
    }

    /**
     * @param object $data
     */
    public function remove($data, ?array $context = null): void
    {
        $manager = $this->getManager($data);

        if ($manager === null) {
            return;
        }

        $manager->remove($data);
        $manager->flush();
    }

    private function getManager($data): ?DoctrineObjectManager
    {
        return \is_object($data) ? $this->managerRegistry->getManagerForClass($this->getObjectClass($data)) : null;
    }
}
