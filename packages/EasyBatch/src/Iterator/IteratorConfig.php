<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Iterator;

final class IteratorConfig
{
    private ?int $batchItemsPerPage = null;

    /**
     * @var callable|null
     */
    private $currentPageCallback;

    private bool $forDispatch = false;

    /**
     * @var callable
     */
    private $func;

    public function __construct(
        private readonly int|string $batchId,
        callable $func,
        private readonly ?string $dependsOnName = null
    ) {
        $this->func = $func;
    }

    public static function create(int|string $batchId, callable $func, ?string $dependsOnName = null): self
    {
        return new self($batchId, $func, $dependsOnName);
    }

    public function forDispatch(?bool $forDispatch = null): self
    {
        $this->forDispatch = $forDispatch ?? true;

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

    public function getFunc(): callable
    {
        return $this->func;
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
        $this->currentPageCallback = $currentPageCallback;

        return $this;
    }
}
