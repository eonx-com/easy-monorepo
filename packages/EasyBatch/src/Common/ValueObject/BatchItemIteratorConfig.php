<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\ValueObject;

use Closure;

final class BatchItemIteratorConfig
{
    private ?int $batchItemsPerPage = null;

    private ?Closure $currentPageCallback = null;

    private ?Closure $extendPaginator = null;

    private bool $forCancel = false;

    private bool $forDispatch = false;

    private readonly Closure $func;

    public function __construct(
        private readonly int|string $batchId,
        callable $func,
        private readonly ?string $dependsOnName = null,
    ) {
        $this->func = $func(...);
    }

    public static function create(int|string $batchId, callable $func, ?string $dependsOnName = null): self
    {
        return new self($batchId, $func, $dependsOnName);
    }

    public function forCancel(?bool $forCancel = null): self
    {
        $this->forCancel = $forCancel ?? true;

        if ($this->forCancel) {
            $this->forDispatch = false;
        }

        return $this;
    }

    public function forDispatch(?bool $forDispatch = null): self
    {
        $this->forDispatch = $forDispatch ?? true;

        if ($this->forDispatch) {
            $this->forCancel = false;
        }

        return $this;
    }

    public function getBatchId(): int|string
    {
        return $this->batchId;
    }

    public function getBatchItemsPerPage(): ?int
    {
        return $this->batchItemsPerPage;
    }

    public function getCurrentPageCallback(): ?callable
    {
        return $this->currentPageCallback;
    }

    public function getDependsOnName(): ?string
    {
        return $this->dependsOnName;
    }

    public function getExtendPaginator(): ?callable
    {
        return $this->extendPaginator;
    }

    public function getFunc(): callable
    {
        return $this->func;
    }

    public function isForCancel(): bool
    {
        return $this->forCancel;
    }

    public function isForDispatch(): bool
    {
        return $this->forDispatch;
    }

    public function setBatchItemsPerPage(int $batchItemsPerPage): self
    {
        $this->batchItemsPerPage = $batchItemsPerPage;

        return $this;
    }

    public function setCurrentPageCallback(callable $currentPageCallback): self
    {
        $this->currentPageCallback = $currentPageCallback(...);

        return $this;
    }

    public function setExtendPaginator(callable $extendPaginator): self
    {
        $this->extendPaginator = $extendPaginator(...);

        return $this;
    }
}
