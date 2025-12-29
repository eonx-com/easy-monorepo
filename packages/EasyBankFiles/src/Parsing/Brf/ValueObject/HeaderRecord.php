<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Brf\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string getBillerCode()
 * @method string getBillerCreditAccount()
 * @method string getBillerCreditBSB()
 * @method string getBillerShortName()
 * @method string getFileCreationDate()
 * @method string getFileCreationTime()
 * @method string getFiller()
 */
final class HeaderRecord extends AbstractResult
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
            'billerCreditAccount',
            'billerCreditBSB',
            'billerShortName',
            'fileCreationDate',
            'fileCreationTime',
            'filler',
        ];
    }
}
