<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\Interfaces\StoreInterface;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;

final class DoctrineDbalStore implements StoreInterface
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \EonX\EasyRandom\Interfaces\RandomGeneratorInterface
     */
    private $random;

    /**
     * @var string
     */
    private $table;

    public function __construct(
        RandomGeneratorInterface $random,
        EntityManagerInterface $entityManager,
        string $table
    ) {
        $this->entityManager = $entityManager;
        $this->random = $random;
        $this->table = $table;
    }

    public function generateActivityLogId(): string
    {
        return $this->random->uuidV4();
    }

    public function getIdentifier(object $subject): ?string
    {
        $unitOfWork = $this->entityManager->getUnitOfWork();

        if ($unitOfWork->isInIdentityMap($subject) === false) {
            return null;
        }

        $identifier = $unitOfWork->getSingleIdentifierValue($subject);

        return (string)$identifier;
    }

    public function store(ActivityLogEntry $logEntry): ActivityLogEntry
    {
        $data = [
            'id' => $this->random->uuidV4(),
            'created_at' => $logEntry->getCreatedAt(),
            'updated_at' => $logEntry->getUpdatedAt(),
            'actor_type' => $logEntry->getActorType(),
            'actor_id' => $logEntry->getActorId(),
            'actor_name' => $logEntry->getActorName(),
            'action' => $logEntry->getAction(),
            'subject_type' => $logEntry->getSubjectType(),
            'subject_id' => $logEntry->getSubjectId(),
            'data' => $logEntry->getData(),
            'old_data' => $logEntry->getOldData(),
        ];

        $this->entityManager->getConnection()
            ->insert($this->table, $data);

        return $logEntry;
    }
}
