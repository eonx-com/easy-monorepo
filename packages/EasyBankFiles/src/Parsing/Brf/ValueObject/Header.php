<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Brf\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string|null getBillerCode()
 * @method string|null getBillerShortName()
 * @method string|null getBillerCreditBSB()
 * @method string|null getBillerCreditAccount()
 * @method string|null getFileCreationDate()
 * @method string|null getFileCreationTime()
 * @method string|null getRestOfRecord()
 */
final class Header extends AbstractResult
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
