<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Ach\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string getBatchCount()
 * @method string getBlockCount()
 * @method string getCode()
 * @method string getEntryAddendaCount()
 * @method string getEntryHash()
 * @method string getReserved()
 * @method string getTotalCreditAmount()
 * @method string getTotalDebitAmount()
 */
final class FileControl extends AbstractResult
{
    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return [
            'batchCount',
            'blockCount',
            'code',
            'entryAddendaCount',
            'entryHash',
            'reserved',
            'totalCreditAmount',
            'totalDebitAmount',
        ];
    }
}
