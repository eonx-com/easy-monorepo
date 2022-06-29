<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Csv;

interface CsvContentsProviderInterface
{
    /**
     * Returns an iterable where each item is a line of csv contents.
     *
     * @return iterable<mixed>
     */
    public function getContents(): iterable;
}
