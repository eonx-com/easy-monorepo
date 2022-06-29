<?php

declare(strict_types=1);

namespace EonX\EasyCore\Csv;

/**
 * @deprecated since 4.1, will be removed in 5.0. Use Eonx\EasyUtils\Csv\CsvWithHeadersParserInterface.
 */
interface CsvWithHeadersParserInterface
{
    /**
     * @return iterable<mixed>
     */
    public function parse(CsvContentsProviderInterface $contentsProvider): iterable;
}
