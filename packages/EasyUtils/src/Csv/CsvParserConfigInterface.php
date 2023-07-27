<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Csv;

interface CsvParserConfigInterface
{
    /**
     * @return string[]|null
     */
    public function getGroupPrefixes(): ?array;

    /**
     * @return callable[]
     */
    public function getRecordTransformers(): array;

    /**
     * @return string[]|null
     */
    public function getRequiredHeaders(): ?array;

    public function hasGroupPrefixes(): bool;

    public function hasRequiredHeaders(): bool;

    public function ignoreEmptyRecords(): bool;
}
