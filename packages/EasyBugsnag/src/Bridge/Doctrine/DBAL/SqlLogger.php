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
     * @var \Bugsnag\Client
     */
    private $client;

    /**
     * @var \Doctrine\DBAL\Driver\Connection
     */
    private $conn;

    /**
     * @var null|string
     */
    private $connName;

    /**
     * @var null|\Doctrine\DBAL\Logging\SQLLogger
     */
    private $decorated;

    /**
     * @var null|mixed[]
     */
    private $params;

    /**
     * @var null|string
     */
    private $sql;

    /**
     * @var null|float
     */
    private $start;

    /**
     * @var null|mixed[]
     */
    private $types;

    public function __construct(
        Client $client,
        Connection $conn,
        ?string $connName = null,
        ?BaseSqlLoggerInterface $decorated = null
    ) {
        $this->client = $client;
        $this->conn = $conn;
        $this->connName = $connName;
        $this->decorated = $decorated;
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
            'SQL' => $this->sql,
            'Params' => \json_encode($this->params),
            'Types' => \json_encode($this->types),
            'Time (ms)' => \number_format((\microtime(true) - $this->start) * 1000, 2),
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
