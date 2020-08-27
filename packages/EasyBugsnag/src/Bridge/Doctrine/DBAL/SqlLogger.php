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
     * @var string
     */
    private $connName;

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

    public function __construct(Client $client, Connection $conn, string $connName)
    {
        $this->client = $client;
        $this->conn = $conn;
        $this->connName = $connName;
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
    }

    public function stopQuery(): void
    {
        $name = \sprintf('SQL query | %s (%s)', $this->connName, $this->conn->getDatabasePlatform()->getName());

        $this->client->leaveBreadcrumb($name, Breadcrumb::PROCESS_TYPE, [
            'DB' => $this->conn->getDatabase(),
            'SQL' => $this->sql,
            'Params' => \json_encode($this->params),
            'Types' => \json_encode($this->types),
            'Time (ms)' => \number_format((\microtime(true) - $this->start) * 1000, 2),
        ]);

        $this->sql = null;
        $this->params = null;
        $this->types = null;
        $this->start = null;
    }
}
