<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Doctrine\Store;

use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use EonX\EasyRandom\Generator\RandomGeneratorInterface;
use EonX\EasyWebhook\Common\Cleaner\DataCleanerInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Helper\WebhookResultHelper;
use EonX\EasyWebhook\Common\Store\ResultStoreInterface;

final class DoctrineDbalResultStore extends AbstractDoctrineDbalStore implements ResultStoreInterface
{
    public const DEFAULT_TABLE = 'easy_webhook_results';

    public function __construct(
        RandomGeneratorInterface $random,
        Connection $connection,
        DataCleanerInterface $dataCleaner,
        ?string $table = null,
    ) {
        parent::__construct($random, $connection, $dataCleaner, $table ?? self::DEFAULT_TABLE);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Nette\Utils\JsonException
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function store(WebhookResultInterface $result): WebhookResultInterface
    {
        $now = Carbon::now('UTC');
        $data = WebhookResultHelper::getResultData($result);
        $data['updated_at'] = $now;

        // New result with no id
        if ($result->getId() === null) {
            $result->setId($this->random->uuid());

            $data['id'] = $result->getId();
            $data['created_at'] = $now;

            $this->connection->insert($this->table, $this->formatData($data));

            return $result;
        }

        // New result with id
        if ($this->existsInDb($result->getId()) === false) {
            $data['id'] = $result->getId();
            $data['created_at'] = $now;

            $this->connection->insert($this->table, $this->formatData($data));

            return $result;
        }

        // Update existing result
        $this->connection->update($this->table, $this->formatData($data), [
            'id' => $result->getId(),
        ]);

        return $result;
    }
}
