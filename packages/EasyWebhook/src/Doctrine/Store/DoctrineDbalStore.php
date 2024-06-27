<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Doctrine\Store;

use Carbon\Carbon;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyPagination\Paginators\DoctrineDbalLengthAwarePaginator;
use EonX\EasyRandom\Generator\RandomGeneratorInterface;
use EonX\EasyWebhook\Common\Cleaner\DataCleanerInterface;
use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Exception\InvalidDateTimeException;
use EonX\EasyWebhook\Common\Store\SendAfterStoreInterface;
use EonX\EasyWebhook\Common\Store\StoreInterface;

final class DoctrineDbalStore extends AbstractDoctrineDbalStore implements StoreInterface, SendAfterStoreInterface
{
    public function __construct(
        RandomGeneratorInterface $random,
        Connection $conn,
        DataCleanerInterface $dataCleaner,
        ?string $table = null,
    ) {
        parent::__construct($random, $conn, $dataCleaner, $table ?? self::DEFAULT_TABLE);
    }

    public function find(string $id): ?WebhookInterface
    {
        $sql = \sprintf('SELECT * FROM %s WHERE id = :id', $this->table);

        $data = $this->conn->fetchAssociative($sql, [
            'id' => $id,
        ]);

        return \is_array($data) ? $this->instantiateWebhook($data) : null;
    }

    public function findDueWebhooks(
        PaginationInterface $pagination,
        ?DateTimeInterface $sendAfter = null,
        ?string $timezone = null,
    ): LengthAwarePaginatorInterface {
        $sendAfter = $sendAfter !== null
            ? Carbon::createFromFormat(self::DATETIME_FORMAT, $sendAfter->format(self::DATETIME_FORMAT), $timezone)
            : Carbon::now($timezone);

        if ($sendAfter instanceof Carbon === false) {
            throw new InvalidDateTimeException(\sprintf(
                'Could not instantiate DateTime for %s::%s',
                self::class,
                __METHOD__
            ));
        }

        $paginator = new DoctrineDbalLengthAwarePaginator($pagination, $this->conn, $this->table);

        $paginator
            ->setFilterCriteria(static function (QueryBuilder $queryBuilder) use ($sendAfter): void {
                $queryBuilder
                    ->where('status = :status AND send_after < :sendAfter')
                    ->setParameters([
                        'sendAfter' => $sendAfter->format(self::DATETIME_FORMAT),
                        'status' => WebhookInterface::STATUS_PENDING,
                    ])
                    ->orderBy('created_at');
            })
            ->setTransformer(fn (array $item): WebhookInterface => $this->instantiateWebhook($item)
                ->bypassSendAfter(true));

        return $paginator;
    }

    public function generateWebhookId(): string
    {
        return $this->random->uuid();
    }

    public function store(WebhookInterface $webhook): WebhookInterface
    {
        $now = Carbon::now('UTC');
        $data = \array_merge($webhook->getExtra() ?? [], $webhook->toArray());
        $data['class'] = $webhook::class;
        $data['updated_at'] = $now;

        // New result with no id
        if ($webhook->getId() === null) {
            $webhook->id($this->random->uuid());

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
     * @return \EonX\EasyWebhook\Common\Entity\WebhookInterface
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

        // Recover extra
        $extra = [];
        foreach ($data as $column => $value) {
            if (\in_array($column, self::DEFAULT_COLUMNS, true)) {
                continue;
            }

            $extra[$column] = $value;
        }

        // Webhook from the store are already configured
        return $class::fromArray($data)
            ->extra($extra)
            ->configured(true);
    }
}
