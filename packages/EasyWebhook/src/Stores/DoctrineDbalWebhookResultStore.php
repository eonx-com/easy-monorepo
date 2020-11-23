<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Stores;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Doctrine\DBAL\Connection;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Webhook;
use EonX\EasyWebhook\WebhookResult;
use Nette\Utils\Json;

final class DoctrineDbalWebhookResultStore extends AbstractIdAwareWebhookResultStore
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $conn;

    /**
     * @var string
     */
    private $table;

    public function __construct(Connection $conn, RandomGeneratorInterface $random, ?string $table = null)
    {
        $this->conn = $conn;
        $this->table = $table ?? 'easy_webhooks';

        parent::__construct($random);
    }

    public function find(string $id): ?WebhookResultInterface
    {
        $sql = \sprintf('SELECT * FROM %s WHERE id = :id', $this->getTableForQuery());

        $data = $this->conn->fetchAssociative($sql, [
            'id' => $id,
        ]);

        if (\is_array($data) === false) {
            return null;
        }

        $class = $data['class'] ?? Webhook::class;

        // Quick fix, maybe we will need to think about something better if needed
        if (\is_string($data['http_options'] ?? null)) {
            $data['http_options'] = \json_decode($data['http_options'], true) ?? $data['http_options'];
        }

        return new WebhookResult($class::fromArray($data)->id($id));
    }

    public function store(WebhookResultInterface $result): WebhookResultInterface
    {
        $now = Carbon::now('UTC');
        $data = $this->getData($result, $now);
        $webhook = $result->getWebhook();

        // New webhook with no id
        if ($webhook->getId() === null) {
            $data['id'] = $this->generateWebhookId();
            $data['created_at'] = $now;

            $this->conn->insert($this->table, $this->formatData($data));

            $webhook->id($data['id']);

            return $result;
        }

        // New webhook with id
        if ($this->existsInDb($webhook->getId()) === false) {
            $data['id'] = $webhook->getId();
            $data['created_at'] = $now;

            $this->conn->insert($this->table, $this->formatData($data));

            return $result;
        }

        // Update existing webhook
        $this->conn->update($this->table, $this->formatData($data), [
            'id' => $webhook->getId(),
        ]);

        return $result;
    }

    private function existsInDb(string $id): bool
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
    private function getData(WebhookResultInterface $result, CarbonInterface $now): array
    {
        $webhook = $result->getWebhook();
        $response = $result->getResponse();
        $throwable = $result->getThrowable();

        // Merge extra so each of them is separate column
        $data = \array_merge($webhook->getExtra() ?? [], $webhook->toArray());

        // Always set updated_at
        $data['updated_at'] = $now;

        // Add class to be able to instantiate when fetching from store
        $data['class'] = \get_class($webhook);

        if ($response !== null) {
            $data['response'] = [
                'content' => $response->getContent(false),
                'headers' => $response->getHeaders(false),
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
