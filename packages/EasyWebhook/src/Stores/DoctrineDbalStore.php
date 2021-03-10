<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Stores;

use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use EonX\EasyPagination\Paginators\DoctrineDbalLengthAwarePaginator;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyWebhook\Exceptions\InvalidDateTimeException;
use EonX\EasyWebhook\Interfaces\Stores\SendAfterStoreInterface;
use EonX\EasyWebhook\Interfaces\Stores\StoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Webhook;

final class DoctrineDbalStore extends AbstractDoctrineDbalStore implements StoreInterface, SendAfterStoreInterface
{
    public function __construct(RandomGeneratorInterface $random, Connection $conn, ?string $table = null)
    {
        parent::__construct($random, $conn, $table ?? 'easy_webhooks');
    }

    public function find(string $id): ?WebhookInterface
    {
        $sql = \sprintf('SELECT * FROM %s WHERE id = :id', $this->getTableForQuery());

        $data = $this->conn->fetchAssociative($sql, [
            'id' => $id,
        ]);

        return \is_array($data) ? $this->instantiateWebhook($data) : null;
    }

    public function findDueWebhooks(
        StartSizeDataInterface $startSize,
        ?\DateTimeInterface $sendAfter = null,
        ?string $timezone = null
    ): LengthAwarePaginatorInterface {
        $sendAfter = $sendAfter !== null
            ? Carbon::createFromFormat(self::DATETIME_FORMAT, $sendAfter->format(self::DATETIME_FORMAT))
            : Carbon::now('UTC');

        if ($sendAfter instanceof Carbon === false) {
            throw new InvalidDateTimeException(\sprintf(
                'Could not instantiate DateTime for %s::%s',
                self::class,
                __METHOD__
            ));
        }

        if ($timezone !== null) {
            $sendAfter->setTimezone($timezone);
        }

        $paginator = new DoctrineDbalLengthAwarePaginator($this->conn, $this->table, $startSize);

        $paginator
            ->setCriteria(static function (QueryBuilder $queryBuilder) use ($sendAfter): void {
                $queryBuilder
                    ->where('status = :status AND send_after < :sendAfter')
                    ->setParameters([
                        'status' => WebhookInterface::STATUS_PENDING,
                        'sendAfter' => $sendAfter->format(self::DATETIME_FORMAT),
                    ]);
            })
            ->setTransformer(function (array $item): WebhookInterface {
                return $this->instantiateWebhook($item);
            });

        return $paginator;
    }

    public function generateWebhookId(): string
    {
        return $this->random->uuidV4();
    }

    public function store(WebhookInterface $webhook): WebhookInterface
    {
        $now = Carbon::now('UTC');
        $data = \array_merge($webhook->getExtra() ?? [], $webhook->toArray());
        $data['class'] = \get_class($webhook);

        // New result with no id
        if ($webhook->getId() === null) {
            $webhook->id($this->random->uuidV4());

            $data['id'] = $webhook->getId();
            $data['created_at'] = $now;

            $this->conn->insert($this->table, $this->formatData($data));

            return $webhook;
        }

        // New result with id
        if ($this->existsInDb($webhook->getId()) === false) {
            $data['id'] = $webhook->getId();
            $data['created_at'] = $now;

            $this->conn->insert($this->table, $this->formatData($data));

            return $webhook;
        }

        // Update existing result
        $this->conn->update($this->table, $this->formatData($data), [
            'id' => $webhook->getId(),
        ]);

        return $webhook;
    }

    /**
     * @param mixed[] $data
     *
     * @return \EonX\EasyWebhook\Interfaces\WebhookInterface
     */
    private function instantiateWebhook(array $data): WebhookInterface
    {
        $class = $data['class'] ?? Webhook::class;

        // Quick fix, maybe we will need to think about something better if needed
        if (\is_string($data['http_options'] ?? null)) {
            $data['http_options'] = \json_decode($data['http_options'], true) ?? $data['http_options'];
        }

        if (\is_string($data['send_after'] ?? null)) {
            $data['send_after'] = Carbon::createFromFormat(self::DATETIME_FORMAT, $data['send_after']);
        }

        // Webhook from the store are already configured
        return $class::fromArray($data)->configured(true);
    }
}
