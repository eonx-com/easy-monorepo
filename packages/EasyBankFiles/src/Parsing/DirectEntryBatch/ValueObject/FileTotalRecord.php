<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\DirectEntryBatch\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string|null getBsb()
 * @method string|null getTotalCreditAmount()
 * @method string|null getTotalDebitAmount()
 * @method string|null getTotalNetAmount()
 * @method string|null getTotalRecordCount()
 */
final class FileTotalRecord extends AbstractResult
{
    /**
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return ['bsb', 'totalCreditAmount', 'totalDebitAmount', 'totalNetAmount', 'totalRecordCount'];
    }
}
