<?php

declare(strict_types=1);

namespace EonX\EasyCore\Csv;

/**
 * @deprecated since 4.1, will be removed in 5.0. Use Eonx\EasyUtils\Csv\CsvContentsProviderInterface.
 */
interface CsvContentsProviderInterface
{
    /**
     * Returns an iterable where each item is a line of csv contents.
     *
     * @return iterable<mixed>
     */
    public function getContents(): iterable;
}
