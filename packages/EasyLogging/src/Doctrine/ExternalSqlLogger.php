<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Doctrine;

use Bugsnag\Breadcrumbs\Breadcrumb;
use Doctrine\DBAL\Logging\SQLLogger;
use EonX\EasyLogging\Interfaces\ExternalLogClientInterface;

final class ExternalSqlLogger implements SQLLogger
{
    /**
     * @var \EonX\EasyLogging\Interfaces\ExternalLogClientInterface
     */
    private $client;

    /**
     * @var string
     */
    private $connectionName;

    /**
     * @var null|bool
     */
    private $includeBindings;

    /**
     * @var mixed[]
     */
    private $params;

    /**
     * @var string
     */
    private $sql;

    /**
     * @var float
     */
    protected $start;

    /**
     * DoctrineSqlLogger constructor.
     *
     * @param \EonX\EasyLogging\Interfaces\ExternalLogClientInterface $client
     * @param string $connectionName
     * @param null|bool $includeBindings
     */
    public function __construct(
        ExternalLogClientInterface $client,
        string $connectionName,
        ?bool $includeBindings = null
    ) {
        $this->client = $client;
        $this->connectionName = $connectionName;
        $this->includeBindings = $includeBindings;

        $this->params = [];
    }

    /**
     * Logs a SQL statement somewhere.
     *
     * @param string|mixed $sql The SQL to be executed.
     * @param mixed[]|null $params The SQL parameters.
     * @param int[]|string[]|null $types The SQL parameter types.
     *
     * @return void
     */
    public function startQuery($sql, ?array $params = null, ?array $types = null): void
    {
        // Store sql and bindings to be used in `stopQuery`
        $this->sql = $sql;

        if ($this->includeBindings === true) {
            $this->params = $params ?? [];
        }

        // Used to calculate execution time.
        $this->start = \microtime(true);
    }

    /**
     * Marks the last started query as stopped. This can be used for timing of queries.
     *
     * @return void
     */
    public function stopQuery(): void
    {
        $this->client->leaveBreadcrumb('Query executed', Breadcrumb::PROCESS_TYPE, $this->formatQuery());
    }

    /**
     * Get execution time in milliseconds.
     *
     * @return mixed
     */
    protected function getExecutionTimeInMs()
    {
        return \number_format((\microtime(true) - $this->start) * 1000, 2);
    }

    /**
     * Format the query as breadcrumb metadata.
     *
     * @return mixed[]
     */
    private function formatQuery(): array
    {
        $data = ['sql' => $this->sql];

        foreach ($this->params as $index => $binding) {
            $data[\sprintf('binding %s', $index)] = $binding;
        }

        $data['time'] = \sprintf('%sms', $this->getExecutionTimeInMs());
        $data['connection'] = $this->connectionName;

        return $data;
    }
}
