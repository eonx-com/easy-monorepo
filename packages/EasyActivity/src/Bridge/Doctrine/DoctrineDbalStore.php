<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Doctrine;

use Doctrine\DBAL\Connection;
use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\Interfaces\IdFactoryInterface;
use EonX\EasyActivity\Interfaces\StoreInterface;

final class DoctrineDbalStore implements StoreInterface
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * @var \EonX\EasyActivity\Interfaces\IdFactoryInterface
     */
    private $idFactory;

    /**
     * @var string
     */
    private $table;

    public function __construct(
        IdFactoryInterface $idFactory,
        Connection $connection,
        string $table
    ) {
        $this->connection = $connection;
        $this->idFactory = $idFactory;
        $this->table = $table;
    }

    public function store(ActivityLogEntry $logEntry): ActivityLogEntry
    {
        $data = [
            'id' => $this->idFactory->create(),
            'created_at' => $logEntry->getCreatedAt(),
            'updated_at' => $logEntry->getUpdatedAt(),
            'actor_type' => $logEntry->getActorType(),
            'actor_id' => $logEntry->getActorId(),
            'actor_name' => $logEntry->getActorName(),
            'action' => $logEntry->getAction(),
            'subject_type' => $logEntry->getSubjectType(),
            'subject_id' => $logEntry->getSubjectId(),
            'subject_data' => $logEntry->getSubjectData(),
            'subject_old_data' => $logEntry->getSubjectOldData(),
        ];

        $this->connection->insert($this->table, $data);

        return $logEntry;
    }
}
