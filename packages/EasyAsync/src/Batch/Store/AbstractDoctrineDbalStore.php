<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Batch\Store;

use Doctrine\DBAL\Connection;
use Nette\Utils\Json;

abstract class AbstractDoctrineDbalStore
{
    /**
     * @var string
     */
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $conn;

    /**
     * @var string
     */
    protected $table;

    public function __construct(Connection $conn, string $table)
    {
        $this->conn = $conn;
        $this->table = $table;
    }

    protected function existsInDb(string $id): bool
    {
        $sql = \sprintf('SELECT id FROM %s WHERE id = :id', $this->getTableForQuery());

        return \is_array($this->conn->fetchAssociative($sql, \compact('id')));
    }

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     *
     * @throws \Nette\Utils\JsonException
     */
    protected function formatData(array $data): array
    {
        return \array_map(static function ($value) {
            if (\is_array($value)) {
                return Json::encode($value);
            }

            if ($value instanceof \DateTimeInterface) {
                return $value->format(self::DATETIME_FORMAT);
            }

            if ($value instanceof \Throwable) {
                return Json::encode([
                    'code' => $value->getCode(),
                    'file' => $value->getFile(),
                    'line' => $value->getLine(),
                    'message' => $value->getMessage(),
                    'trace' => $value->getTraceAsString(),
                ]);
            }

            return $value;
        }, $data);
    }

    protected function getTableForQuery(): string
    {
        return \sprintf('`%s`', $this->table);
    }
}
