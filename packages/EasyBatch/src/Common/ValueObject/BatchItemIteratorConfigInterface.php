<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\ValueObject;

use EonX\EasyBatch\Doctrine\ValueObject\BatchItemIteratorConfig;

interface BatchItemIteratorConfigInterface
{
    public static function create(
        int|string $batchId,
        callable $func,
        ?string $dependsOnName = null
    ): BatchItemIteratorConfig;

    public function forCancel(?bool $forCancel = null): BatchItemIteratorConfig;

    public function forDispatch(?bool $forDispatch = null): BatchItemIteratorConfig;

    public function getBatchId(): int|string;

    public function getBatchItemsPerPage(): ?int;

    public function getCurrentPageCallback(): ?callable;

    public function getDependsOnName(): ?string;

    public function getExtendPaginator(): ?callable;

    public function getFunc(): callable;

    public function isForCancel(): bool;

    public function isForDispatch(): bool;

    public function setBatchItemsPerPage(int $batchItemsPerPage
    ): BatchItemIteratorConfig;

    public function setCurrentPageCallback(callable $currentPageCallback
    ): BatchItemIteratorConfig;

    public function setExtendPaginator(callable $extendPaginator
    ): BatchItemIteratorConfig;
}
