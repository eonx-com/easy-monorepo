<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Csv;

interface CsvParserConfigInterface
{
    /**
     * @return null|string[]
     */
    public function getGroupPrefixes(): ?array;

    /**
     * @return callable[]
     */
    public function getRecordTransformers(): array;

    /**
     * @return null|string[]
     */
    public function getRequiredHeaders(): ?array;

    public function hasGroupPrefixes(): bool;

    public function hasRequiredHeaders(): bool;

    public function ignoreEmptyRecords(): bool;
}
