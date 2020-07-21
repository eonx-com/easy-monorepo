<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Stores;

use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookStoreInterface;
use EonX\EasyWebhook\Webhook;
use Nette\Utils\Json;

final class DoctrineDbalWebhookStore implements WebhookStoreInterface
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $conn;

    /**
     * @var \EonX\EasyRandom\Interfaces\RandomGeneratorInterface
     */
    private $random;

    /**
     * @var string
     */
    private $table;

    public function __construct(Connection $conn, RandomGeneratorInterface $random, ?string $table = null)
    {
        $this->conn = $conn;
        $this->random = $random;
        $this->table = $table ?? 'easy_webhooks';
    }

    public function find(string $webhookId): ?WebhookInterface
    {
        $sql = \sprintf('SELECT * FROM %s WHERE id = :id', $this->getTableForQuery());

        $data = $this->conn->fetchAssoc($sql, ['id' => $webhookId]);

        if (\is_array($data) === false) {
            return null;
        }

        $class = $data['class'] ?? Webhook::class;

        return $class::fromArray($data);
    }

    /**
     * @param mixed[] $data
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function store(array $data, ?string $id = null): string
    {
        $now = Carbon::now('UTC');

        $data['updated_at'] = $now;

        if ($id === null) {
            $data['id'] = $this->random->uuidV4();
            $data['created_at'] = $now;

            $this->conn->insert($this->table, $this->formatData($data));

            return $data['id'];
        }

        $this->conn->update($this->getTableForQuery(), $this->formatData($data), [$id]);

        return $id;
    }

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    private function formatData(array $data): array
    {
        return \array_map(static function ($value) {
            if (\is_array($value)) {
                return Json::encode($value);
            }

            if ($value instanceof \DateTimeInterface) {
                return $value->format(self::DATETIME_FORMAT);
            }

            return $value;
        }, $data);
    }

    private function getTableForQuery(): string
    {
        return \sprintf('`%s`', $this->table);
    }
}
