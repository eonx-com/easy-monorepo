<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Nai\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

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
final class FileHeader extends AbstractResult
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
