<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Nai\Results\Files;

use EonX\EasyBankFiles\Parsers\BaseResult;

/**
 * @method string getBlockingFactor()
 * @method string getCode()
 * @method string getFileCreationDate()
 * @method string getFileCreationTime()
 * @method string getFileSequenceNumber()
 * @method string getPhysicalRecordLength()
 * @method string getReceiverId()
 * @method string getSenderId()
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
            'blockingFactor',
            'code',
            'fileCreationDate',
            'fileCreationTime',
            'fileSequenceNumber',
            'physicalRecordLength',
            'receiverId',
            'senderId',
        ];
    }
}
