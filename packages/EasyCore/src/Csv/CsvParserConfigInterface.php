<?php

declare(strict_types=1);

namespace EonX\EasyCore\Csv;

/**
 * @deprecated since 4.1, will be removed in 5.0. Use Eonx\EasyUtils\Csv\CsvParserConfigInterface.
 */
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
