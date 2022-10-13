<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\DirectEntry\Results\Batch;

use DateTimeImmutable;
use EonX\EasyBankFiles\Parsers\BaseResult;

/**
 * @method string|null getDateProcessed()
 * @method string|null getDescription()
 * @method string|null getReelSequenceNumber()
 * @method string|null getUserFinancialInstitution()
 * @method string|null getUserIdSupplyingFile()
 * @method string|null getUserSupplyingFile()
 */
final class Header extends BaseResult
{
    /**
     * @var string Date in string representation pattern
     */
    private const DATE_STRING_PATTERN = 'dmy';

    public function getDateProcessedObject(): ?DateTimeImmutable
    {
        if (\is_string($this->data['dateProcessed'])) {
            $dateTime = DateTimeImmutable::createFromFormat(self::DATE_STRING_PATTERN, $this->data['dateProcessed']);
            if ($dateTime !== false) {
                return $dateTime->setTime(0, 0);
            }
        }

        return null;
    }

    /**
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return [
            'dateProcessed',
            'description',
            'reelSequenceNumber',
            'userFinancialInstitution',
            'userIdSupplyingFile',
            'userSupplyingFile',
        ];
    }
}
