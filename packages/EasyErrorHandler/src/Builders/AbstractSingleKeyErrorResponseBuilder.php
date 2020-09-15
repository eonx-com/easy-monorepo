<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Builders;

use Throwable;

abstract class AbstractSingleKeyErrorResponseBuilder extends AbstractErrorResponseBuilder
{
    /**
     * @var null|string
     */
    protected $key;

    public function __construct(?string $key = null, ?int $priority = null)
    {
        $this->key = $key;

        parent::__construct($priority);
    }

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public function buildData(Throwable $throwable, array $data): array
    {
        $value = $this->doBuildValue($throwable, $data);

        if ($value !== null) {
            $data[$this->key ?? $this->getDefaultKey()] = $value;
        }

        return parent::buildData($throwable, $data);
    }

    abstract protected function doBuildValue(Throwable $throwable, array $data);

    abstract protected function getDefaultKey(): string;
}
