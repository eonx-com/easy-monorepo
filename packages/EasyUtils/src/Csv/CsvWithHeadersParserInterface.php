<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Csv;

interface CsvWithHeadersParserInterface
{
    public function parse(
        CsvContentsProviderInterface $contentsProvider,
        ?CsvParserConfigInterface $config = null,
    ): iterable;
}
