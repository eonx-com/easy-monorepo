<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Doctrine\Store;

use Carbon\Carbon;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use EonX\EasyPagination\Pagination\PaginationInterface;
use EonX\EasyPagination\Paginator\DoctrineDbalLengthAwarePaginator;
use EonX\EasyPagination\Paginator\LengthAwarePaginatorInterface;
use EonX\EasyRandom\Generator\RandomGeneratorInterface;
use EonX\EasyWebhook\Common\Cleaner\DataCleanerInterface;
use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Enum\WebhookStatus;
use EonX\EasyWebhook\Common\Store\SendAfterStoreInterface;
use EonX\EasyWebhook\Common\Store\StoreInterface;

final class DoctrineDbalStore extends AbstractDoctrineDbalStore implements StoreInterface, SendAfterStoreInterface
{
    public const DEFAULT_TABLE = 'easy_webhooks';

    public function __construct(
        RandomGeneratorInterface $random,
        Connection $connection,
        DataCleanerInterface $dataCleaner,
        ?string $table = null,
    ) {
        parent::__construct($random, $connection, $dataCleaner, $table ?? self::DEFAULT_TABLE);
    }

    public function find(string $id): ?WebhookInterface
    {
        $sql = \sprintf('SELECT * FROM %s WHERE id = :id', $this->table);

        $data = $this->connection->fetchAssociative($sql, [
            'id' => $id,
        ]);

        return \is_array($data) ? $this->instantiateWebhook($data) : null;
    }

    public function findDueWebhooks(
        PaginationInterface $pagination,
        ?DateTimeInterface $sendAfter = null,
        ?string $timezone = null,
    ): LengthAwarePaginatorInterface {
        $sendAfter = $sendAfter !== null ? Carbon::instance($sendAfter) : Carbon::now($timezone);

        if ($timezone !== null) {
            $sendAfter = $sendAfter->shiftTimezone($timezone);
        }

        $paginator = new DoctrineDbalLengthAwarePaginator($pagination, $this->connection, $this->table);

        $paginator
            ->setFilterCriteria(static function (QueryBuilder $queryBuilder) use ($sendAfter): void {
                $queryBuilder
                    ->where('status = :status AND send_after < :sendAfter')
                    ->setParameters([
                        'sendAfter' => $sendAfter->format(self::DATETIME_FORMAT),
                        'status' => WebhookStatus::Pending->value,
                    ]);
            })
            ->setGetItemsCriteria(static function (QueryBuilder $queryBuilder): void {
                $queryBuilder->orderBy('created_at');
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

            $this->connection->insert($this->table, $this->formatData($data));

            return $webhook;
        }

        // New result with id
        if ($this->existsInDb($webhook->getId()) === false) {
            $data['id'] = $webhook->getId();
            $data['created_at'] = $now;

            $this->connection->insert($this->table, $this->formatData($data));

            return $webhook;
        }

        // Update existing result
        $this->connection->update($this->table, $this->formatData($data), [
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
            $data['send_after'] = Carbon::parse($data['send_after']);
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
