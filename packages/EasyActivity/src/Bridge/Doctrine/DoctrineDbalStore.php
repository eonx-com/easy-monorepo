<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Doctrine;

use Doctrine\DBAL\Connection;
use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\Interfaces\IdFactoryInterface;
use EonX\EasyActivity\Interfaces\StoreInterface;

final class DoctrineDbalStore implements StoreInterface
{
    public function __construct(
        private IdFactoryInterface $idFactory,
        private Connection $connection,
        private string $table,
    ) {
    }

    public function store(ActivityLogEntry $logEntry): ActivityLogEntry
    {
        $data = [
            'action' => $logEntry->getAction(),
            'actor_id' => $logEntry->getActorId(),
            'actor_name' => $logEntry->getActorName(),
            'actor_type' => $logEntry->getActorType(),
            'created_at' => $logEntry->getCreatedAt()
                ->format('Y-m-d H:i:s.u'),
            'id' => $this->idFactory->create(),
            'subject_data' => $logEntry->getSubjectData(),
            'subject_id' => $logEntry->getSubjectId(),
            'subject_old_data' => $logEntry->getSubjectOldData(),
            'subject_type' => $logEntry->getSubjectType(),
            'updated_at' => $logEntry->getUpdatedAt()
                ->format('Y-m-d H:i:s.u'),
        ];

        $this->connection->insert($this->table, $data);

        return $logEntry;
    }
}
