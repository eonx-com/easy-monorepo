<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Doctrine\DBAL;

use Bugsnag\Breadcrumbs\Breadcrumb;
use Bugsnag\Client;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Logging\SQLLogger as BaseSqlLoggerInterface;

final class SqlLogger implements BaseSqlLoggerInterface
{
    /**
     * @var \Bugsnag\Client
     */
    private $client;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $conn;

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

    public function __construct(Client $client, Connection $conn, ?BaseSqlLoggerInterface $decorated = null)
    {
        $this->client = $client;
        $this->conn = $conn;
        $this->decorated = $decorated;
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
        $this->client->leaveBreadcrumb('Query executed', Breadcrumb::PROCESS_TYPE, [
            'database' => $this->conn->getDatabase(),
            'platform' => $this->conn->getDatabasePlatform()->getName(),
            'sql' => $this->sql,
            'params' => $this->params,
            'types' => $this->types,
            'time' => \number_format((\microtime(true) - $this->start) * 1000, 2)
        ]);

        $this->sql = null;
        $this->params = null;
        $this->types = null;
        $this->start = null;

        if ($this->decorated !== null) {
            $this->decorated->stopQuery();
        }
    }
}
