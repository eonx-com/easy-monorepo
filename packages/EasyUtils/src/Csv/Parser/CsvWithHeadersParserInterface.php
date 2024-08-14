<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Csv\Parser;

use EonX\EasyUtils\Csv\Provider\CsvContentsProviderInterface;
use EonX\EasyUtils\Csv\ValueObject\CsvParserConfig;

interface CsvWithHeadersParserInterface
{
    public function parse(
        CsvContentsProviderInterface $contentsProvider,
        ?CsvParserConfig $config = null,
    ): iterable;
}
