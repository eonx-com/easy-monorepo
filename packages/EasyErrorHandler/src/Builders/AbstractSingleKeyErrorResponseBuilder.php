<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Builders;

use Throwable;

abstract class AbstractSingleKeyErrorResponseBuilder extends AbstractErrorResponseBuilder
{
    public function __construct(
        protected readonly ?string $key = null,
        ?int $priority = null
    ) {
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

    /**
     * @param mixed[] $data
     */
    abstract protected function doBuildValue(Throwable $throwable, array $data): mixed;

    abstract protected function getDefaultKey(): string;
}
