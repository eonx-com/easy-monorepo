<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Ach\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string getBlockingFactor()
 * @method string getCode()
 * @method string getImmediateDestination()
 * @method string getImmediateDestinationName()
 * @method string getImmediateOrigin()
 * @method string getImmediateOriginName()
 * @method string getFileCreationDate()
 * @method string getFileCreationTime()
 * @method string getFileIdModifier()
 * @method string getFormatCode()
 * @method string getRecordSize()
 * @method string getPriorityCode()
 * @method string getReferenceCode()
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
            'immediateDestination',
            'immediateDestinationName',
            'immediateOrigin',
            'immediateOriginName',
            'fileCreationDate',
            'fileCreationTime',
            'fileIdModifier',
            'formatCode',
            'recordSize',
            'priorityCode',
            'referenceCode',
        ];
    }
}
