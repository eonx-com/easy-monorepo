<?php

declare(strict_types=1);

namespace EonX\EasyCore\Csv;

interface CsvParserConfigInterface
{
    /**
     * @return null|string[]
     */
    public function getGroupPrefixes(): ?array;

    /**
     * @return null|string[]
     */
    public function getRequiredHeaders(): ?array;
}
