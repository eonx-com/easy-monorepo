<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch;

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

    /**
     * Return processed date as a DateTimeImmutable object.
     */
    public function getDateProcessedObject(): ?DateTimeImmutable
    {
        $value = $this->data['dateProcessed'];

        if (\is_string($value) === true &&
            \strlen($value) === 6 &&
            \ctype_digit($value) === true
        ) {
            $dateTime = DateTimeImmutable::createFromFormat(self::DATE_STRING_PATTERN, $value);
            if ($dateTime !== false) {
                return $dateTime->setTime(0, 0);
            }
        }

        return null;
    }

    /**
     * Return object attributes.
     *
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
