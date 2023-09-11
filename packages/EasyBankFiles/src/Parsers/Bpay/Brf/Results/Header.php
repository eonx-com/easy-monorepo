<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Bpay\Brf\Results;

use EonX\EasyBankFiles\Parsers\BaseResult;

/**
 * @method string|null getBillerCode()
 * @method string|null getBillerShortName()
 * @method string|null getBillerCreditBSB()
 * @method string|null getBillerCreditAccount()
 * @method string|null getFileCreationDate()
 * @method string|null getFileCreationTime()
 * @method string|null getRestOfRecord()
 */
final class Header extends BaseResult
{
    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return [
            'billerCode',
            'billerShortName',
            'billerCreditBSB',
            'billerCreditAccount',
            'fileCreationDate',
            'fileCreationTime',
            'restOfRecord',
        ];
    }
}
