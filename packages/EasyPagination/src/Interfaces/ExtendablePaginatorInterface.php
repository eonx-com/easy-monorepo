<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Interfaces;

interface ExtendablePaginatorInterface
{
    public function addCommonCriteria(callable $commonCriteria, ?string $name = null): self;

    public function addFilterCriteria(callable $filterCriteria, ?string $name = null): self;

    public function addGetItemsCriteria(callable $getItemsCriteria, ?string $name = null): self;

    public function removeCommonCriteria(string $name): self;

    public function removeFilterCriteria(string $name): self;

    public function removeGetItemsCriteria(string $name): self;

    public function setCommonCriteria(?callable $commonCriteria = null, ?string $name = null): self;

    public function setFilterCriteria(?callable $filterCriteria = null, ?string $name = null): self;

    public function setGetItemsCriteria(?callable $getItemsCriteria = null, ?string $name = null): self;
}
