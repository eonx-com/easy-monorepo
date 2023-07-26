<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Stores;

use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyUtils\Helpers\ErrorDetailsHelper;
use EonX\EasyWebhook\Interfaces\Stores\DataCleanerInterface;
use EonX\EasyWebhook\Interfaces\Stores\ResultStoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

final class DoctrineDbalResultStore extends AbstractDoctrineDbalStore implements ResultStoreInterface
{
    public function __construct(
        RandomGeneratorInterface $random,
        Connection $conn,
        DataCleanerInterface $dataCleaner,
        ?string $table = null,
    ) {
        parent::__construct($random, $conn, $dataCleaner, $table ?? 'easy_webhook_results');
    }

    public function store(WebhookResultInterface $result): WebhookResultInterface
    {
        $now = Carbon::now('UTC');
        $data = $this->getData($result, $now);

        // New result with no id
        if ($result->getId() === null) {
            $result->setId($this->random->uuid());

            $data['id'] = $result->getId();
            $data['created_at'] = $now;

            $this->conn->insert($this->table, $this->formatData($data));

            return $result;
        }

        // New result with id
        if ($this->existsInDb($result->getId()) === false) {
            $data['id'] = $result->getId();
            $data['created_at'] = $now;

            $this->conn->insert($this->table, $this->formatData($data));

            return $result;
        }

        // Update existing result
        $this->conn->update($this->table, $this->formatData($data), [
            'id' => $result->getId(),
        ]);

        return $result;
    }

    /**
     * @return mixed[]
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function getData(WebhookResultInterface $result, Carbon $now): array
    {
        $webhook = $result->getWebhook();
        $response = $result->getResponse();
        $throwable = $result->getThrowable();

        $data = [
            'http_options' => $webhook->getHttpClientOptions(),
            'method' => $webhook->getMethod(),
            'updated_at' => $now,
            'url' => $webhook->getUrl(),
            'webhook_class' => $webhook::class,
            'webhook_id' => $webhook->getId(),
        ];

        if ($response !== null) {
            $data['response'] = [
                'content' => $response->getContent(false),
                'headers' => $response->getHeaders(false),
                'info' => $response->getInfo(),
                'status_code' => $response->getStatusCode(),
            ];
        }

        if ($throwable !== null) {
            $data['throwable'] = ErrorDetailsHelper::resolveSimpleDetails($throwable);
        }

        return $data;
    }
}
