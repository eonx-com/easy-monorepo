<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Stores;

use Doctrine\DBAL\Connection;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;

abstract class AbstractDoctrineDbalStore extends AbstractStore
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $conn;

    /**
     * @var string
     */
    protected $table;

    public function __construct(RandomGeneratorInterface $random, Connection $conn, string $table)
    {
        $this->conn = $conn;
        $this->table = $table;

        parent::__construct($random);
    }

    protected function existsInDb(string $id): bool
    {
        $sql = \sprintf('SELECT id FROM %s WHERE id = :id', $this->getTableForQuery());

        return \is_array($this->conn->fetchAssociative($sql, \compact('id')));
    }

    protected function getTableForQuery(): string
    {
        return \sprintf('`%s`', $this->table);
    }
}
