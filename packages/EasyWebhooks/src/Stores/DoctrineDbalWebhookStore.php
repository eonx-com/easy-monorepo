<?php

declare(strict_types=1);

namespace EonX\EasyWebhooks\Stores;

use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyWebhooks\Interfaces\WebhookStoreInterface;
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

    /**
     * @param mixed[] $data
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function store(array $data, ?string $id = null): void
    {
        $now = Carbon::now('UTC');
        
        $data['updated_at'] = $now;

        if ($id === null) {
            $data['id'] = $this->random->uuidV4();
            $data['created_at'] = $now;

            $this->conn->insert($this->table, $this->formatData($data));

            return;
        }

        $this->conn->update($this->table, $this->formatData($data), [$id]);
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
}
