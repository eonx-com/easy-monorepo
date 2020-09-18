<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Doctrine;

use Bugsnag\Breadcrumbs\Breadcrumb;
use Doctrine\DBAL\Logging\SQLLogger;
use EonX\EasyLogging\Interfaces\ExternalLogClientInterface;

/**
 * @deprecated since 2.4, will be removed in 3.0. Bugsnag implementation will be reworked.
 */
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
    private $start;

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
     * @param string|mixed $sql The SQL to be executed.
     * @param mixed[]|null $params The SQL parameters.
     * @param int[]|string[]|null $types The SQL parameter types.
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

    public function stopQuery(): void
    {
        $this->client->leaveBreadcrumb('Query executed', Breadcrumb::PROCESS_TYPE, $this->formatQuery());
    }

    /**
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

    private function getExecutionTimeInMs(): string
    {
        return \number_format((\microtime(true) - $this->start) * 1000, 2);
    }
}
