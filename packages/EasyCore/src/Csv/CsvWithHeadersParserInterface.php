<?php

declare(strict_types=1);

namespace EonX\EasyCore\Csv;

interface CsvWithHeadersParserInterface
{
    /**
     * @return iterable<mixed>
     */
    public function parse(CsvContentsProviderInterface $contentsProvider): iterable;
}
