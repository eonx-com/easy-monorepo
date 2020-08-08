<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Stores;

use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface;
use EonX\EasyWebhook\Webhook;
use EonX\EasyWebhook\WebhookResult;
use Nette\Utils\Json;

final class DoctrineDbalWebhookResultStore implements WebhookResultStoreInterface
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

    public function find(string $id): ?WebhookResultInterface
    {
        $sql = \sprintf('SELECT * FROM %s WHERE id = :id', $this->getTableForQuery());

        $data = $this->conn->fetchAssoc($sql, ['id' => $id]);

        if (\is_array($data) === false) {
            return null;
        }

        $class = $data['class'] ?? Webhook::class;

        return new WebhookResult($class::fromArray($data)->id($id));
    }

    public function store(WebhookResultInterface $result): WebhookResultInterface
    {
        $data = $this->getData($result);
        $now = Carbon::now('UTC');

        $data['updated_at'] = $now;

        if ($result->getWebhook()->getId() === null) {
            $data['id'] = $this->random->uuidV4();
            $data['created_at'] = $now;

            $this->conn->insert($this->table, $this->formatData($data));

            $result->getWebhook()->id($data['id']);

            return $result;
        }

        $this->conn->update($this->table, $this->formatData($data), ['id' => $result->getWebhook()->getId()]);

        return $result;
    }

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     *
     * @throws \Nette\Utils\JsonException
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

    /**
     * @return mixed[]
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function getData(WebhookResultInterface $result): array
    {
        $webhook = $result->getWebhook();
        $response = $result->getResponse();
        $throwable = $result->getThrowable();

        // Merge extra so each of them is separate column
        $data = \array_merge($webhook->getExtra() ?? [], $webhook->toArray());

        // Add class to be able to instantiate when fetching from store
        $data['class'] = \get_class($webhook);

        if ($response !== null) {
            $data['response'] = [
                'content' => $response->getContent(),
                'headers' => $response->getHeaders(),
                'info' => $response->getInfo(),
                'status_code' => $response->getStatusCode(),
            ];
        }

        if ($throwable !== null) {
            $data['throwable'] = [
                'code' => $throwable->getCode(),
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'message' => $throwable->getMessage(),
                'trace' => $throwable->getTraceAsString(),
            ];
        }

        return $data;
    }

    private function getTableForQuery(): string
    {
        return \sprintf('`%s`', $this->table);
    }
}
