<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Csv;

interface CsvWithHeadersParserInterface
{
    /**
     * @return iterable<mixed>
     */
    public function parse(
        CsvContentsProviderInterface $contentsProvider,
        ?CsvParserConfigInterface $config = null
    ): iterable;
}
