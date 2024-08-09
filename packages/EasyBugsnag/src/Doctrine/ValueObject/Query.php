<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Doctrine\ValueObject;

use Doctrine\DBAL\ParameterType;

final class Query
{
    private ?float $duration = null;

    private array $params = [];

    private float $start;

    /**
     * @var array<\Doctrine\DBAL\ParameterType|int>
     */
    private array $types = [];

    public function __construct(
        private readonly string $sql,
    ) {
        $this->start = \microtime(true);
    }

    public function __clone()
    {
        $copy = [];
        foreach ($this->params as $param => $valueOrVariable) {
            $copy[$param] = $valueOrVariable;
        }
        $this->params = $copy;
    }

    /**
     * Query duration in seconds.
     */
    public function getDuration(): ?float
    {
        return $this->duration;
    }

    /**
     * @return array<int, string|int|float>
     */
    public function getParams(): array
    {
        return $this->params;
    }

    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * @return array<int, int|\Doctrine\DBAL\ParameterType>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    public function setParam(string|int $param, mixed &$variable, ParameterType|int $type): void
    {
        // Numeric indexes start at 0 in profiler
        $idx = \is_int($param) ? $param - 1 : $param;

        $this->params[$idx] = &$variable;
        $this->types[$idx] = $type;
    }

    public function setValue(string|int $param, mixed $value, ParameterType|int $type): void
    {
        // Numeric indexes start at 0 in profiler
        $idx = \is_int($param) ? $param - 1 : $param;

        $this->params[$idx] = $value;
        $this->types[$idx] = $type;
    }

    /**
     * @param array<string|int, string|int|float> $values
     */
    public function setValues(array $values): void
    {
        foreach ($values as $param => $value) {
            $this->setValue($param, $value, ParameterType::STRING);
        }
    }

    public function start(): void
    {
        $this->start = \microtime(true);
    }

    public function stop(): void
    {
        $this->duration = \microtime(true) - $this->start;
    }
}
