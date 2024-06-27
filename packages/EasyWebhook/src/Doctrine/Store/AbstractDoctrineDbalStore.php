<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Doctrine\Store;

use Doctrine\DBAL\Connection;
use EonX\EasyRandom\Generator\RandomGeneratorInterface;
use EonX\EasyWebhook\Common\Cleaner\DataCleanerInterface;
use EonX\EasyWebhook\Common\Store\AbstractStore;

abstract class AbstractDoctrineDbalStore extends AbstractStore
{
    public function __construct(
        RandomGeneratorInterface $random,
        protected Connection $conn,
        DataCleanerInterface $dataCleaner,
        protected string $table,
    ) {
        parent::__construct($random, $dataCleaner);
    }

    protected function existsInDb(string $id): bool
    {
        $sql = \sprintf('SELECT id FROM %s WHERE id = :id', $this->table);

        return \is_array($this->conn->fetchAssociative($sql, \compact('id')));
    }
}
