<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Doctrine\DBAL;

use Bugsnag\Breadcrumbs\Breadcrumb;
use Bugsnag\Client;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Logging\SQLLogger as BaseSqlLoggerInterface;

final class SqlLogger implements BaseSqlLoggerInterface
{
    /**
     * @var mixed[]|null
     */
    private ?array $params = null;

    private ?string $sql = null;

    private ?float $start = null;

    /**
     * @var mixed[]|null
     */
    private ?array $types = null;

    public function __construct(
        private Client $client,
        private Connection $conn,
        private ?string $connName = null,
        private ?BaseSqlLoggerInterface $decorated = null,
    ) {
    }

    public function getDecorated(): ?BaseSqlLoggerInterface
    {
        return $this->decorated;
    }

    /**
     * @param string $sql
     * @param mixed[]|null $params The SQL parameters.
     * @param int[]|string[]|null $types The SQL parameter types.
     */
    public function startQuery($sql, ?array $params = null, ?array $types = null): void
    {
        $this->sql = $sql;
        $this->params = $params;
        $this->types = $types;
        $this->start = \microtime(true);

        if ($this->decorated !== null) {
            $this->decorated->startQuery($sql, $params, $types);
        }
    }

    public function stopQuery(): void
    {
        $name = 'SQL query';

        if ($this->connName !== null) {
            $name .= \sprintf(' | %s', $this->connName);
        }

        $metadata = [
            'Params' => \json_encode($this->params),
            'SQL' => $this->sql,
            'Time (ms)' => \number_format((\microtime(true) - $this->start) * 1000, 2),
            'Types' => \json_encode($this->types),
        ];

        if (\method_exists($this->conn, 'getDatabase')) {
            $metadata['DB'] = $this->conn->getDatabase();
        }

        if (\method_exists($this->conn, 'getDatabasePlatform')) {
            $metadata['Platform'] = $this->conn->getDatabasePlatform()->getName();
        }

        $this->client->leaveBreadcrumb($name, Breadcrumb::PROCESS_TYPE, $metadata);

        $this->sql = null;
        $this->params = null;
        $this->types = null;
        $this->start = null;

        if ($this->decorated !== null) {
            $this->decorated->stopQuery();
        }
    }
}
