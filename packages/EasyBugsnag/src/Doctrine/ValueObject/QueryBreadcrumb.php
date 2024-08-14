<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Doctrine\ValueObject;

use Doctrine\DBAL\ParameterType;

final class QueryBreadcrumb
{
    private ?float $queryDuration = null;

    private array $queryParameters = [];

    private float $queryStartTime;

    /**
     * @var array<\Doctrine\DBAL\ParameterType>
     */
    private array $queryTypes = [];

    private array $queryValues = [];

    public function __construct(
        private readonly string $querySql,
        private readonly string $connectionName,
    ) {
        $this->queryStartTime = \microtime(true);
    }

    public function __clone()
    {
        $copy = [];
        foreach ($this->queryParameters as $param => $valueOrVariable) {
            $copy[$param] = $valueOrVariable;
        }
        $this->queryParameters = $copy;
        $this->queryStartTime = \microtime(true);
    }

    public function getConnectionName(): string
    {
        return $this->connectionName;
    }

    /**
     * Query duration in seconds.
     */
    public function getQueryDuration(): ?float
    {
        if ($this->queryDuration === null) {
            $this->queryDuration = \microtime(true) - $this->queryStartTime;
        }

        return $this->queryDuration;
    }

    public function getQueryParameters(): array
    {
        return $this->queryParameters;
    }

    public function getQuerySql(): string
    {
        return $this->querySql;
    }

    /**
     * @return array<\Doctrine\DBAL\ParameterType>
     */
    public function getQueryTypes(): array
    {
        return $this->queryTypes;
    }

    public function getQueryValues(): array
    {
        return $this->queryValues;
    }

    public function setQueryParameter(int|string $parameter, mixed $value, ParameterType $type): void
    {
        // Numeric indexes start at 0 in profiler
        $index = \is_int($parameter) ? $parameter - 1 : $parameter;

        $this->queryParameters[$index] = $parameter;
        $this->queryValues[$index] = $value;
        $this->queryTypes[$index] = $type;
    }
}
