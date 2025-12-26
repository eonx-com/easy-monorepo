<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\DirectEntryBatch\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string getBsb()
 * @method string getTotalCreditAmount()
 * @method string getTotalDebitAmount()
 * @method string getTotalNetAmount()
 * @method string getTotalRecordCount()
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
