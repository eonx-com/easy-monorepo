<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Doctrine\Store;

use Doctrine\DBAL\Connection;
use EonX\EasyActivity\Common\Entity\ActivityLogEntry;
use EonX\EasyActivity\Common\Factory\IdFactoryInterface;
use EonX\EasyActivity\Common\Store\StoreInterface;

final readonly class DoctrineDbalStore implements StoreInterface
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
            'action' => $logEntry->getAction()
                ->value,
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
