<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch;

use EonX\EasyBankFiles\Parsers\BaseResult;

/**
 * @method string|null getBsb()
 * @method string|null getTotalCreditAmount()
 * @method string|null getTotalDebitAmount()
 * @method string|null getTotalNetAmount()
 * @method string|null getTotalRecordCount()
 */
final class FileTotalRecord extends BaseResult
{
    /**
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return ['bsb', 'totalCreditAmount', 'totalDebitAmount', 'totalNetAmount', 'totalRecordCount'];
    }
}
