<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

interface ErrorCodesProviderInterface
{
    public function setCategorySize(int $categorySize): self;

    public function getNextCategoryToUse(): int;

    /**
     * @return mixed[]
     */
    public function getNextErrorCodeForCategory(): array;

    public function process(): bool;
}
