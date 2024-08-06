<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Builder;

use Throwable;

abstract class AbstractSingleKeyErrorResponseBuilder extends AbstractErrorResponseBuilder
{
    public function __construct(
        protected readonly string $key,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function buildData(Throwable $throwable, array $data): array
    {
        $value = $this->doBuildValue($throwable, $data);

        if ($value !== null) {
            $data[$this->key] = $value;
        }

        return parent::buildData($throwable, $data);
    }

    abstract protected function doBuildValue(Throwable $throwable, array $data): mixed;
}
