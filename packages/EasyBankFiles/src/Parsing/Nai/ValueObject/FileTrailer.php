<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Nai\ValueObject;

use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string getCode()
 * @method string getNumberOfGroups()
 * @method string getNumberOfRecords()
 */
final class FileTrailer extends AbstractResult
{
    use ControlTotalTrait;

    /**
     * Return file control total A.
     */
    public function getFileControlTotalA(): float
    {
        return $this->formatAmount($this->data['fileControlTotalA']);
    }

    /**
     * Return file control total B.
     */
    public function getFileControlTotalB(): float
    {
        return $this->formatAmount($this->data['fileControlTotalB']);
    }

    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return ['code', 'fileControlTotalA', 'fileControlTotalB', 'numberOfGroups', 'numberOfRecords'];
    }
}
