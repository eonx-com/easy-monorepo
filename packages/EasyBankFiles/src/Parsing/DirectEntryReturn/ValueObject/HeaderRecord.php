<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\DirectEntryReturn\ValueObject;

use DateTime;
use DateTimeInterface;
use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string|null getDateProcessed()
 * @method string|null getDescription()
 * @method string|null getDirectEntryUserId()
 * @method string|null getMnemonicOfFinancialInstitution()
 * @method string|null getMnemonicOfSendingMember()
 * @method string|null getReelSequenceNumber()
 */
final class HeaderRecord extends AbstractResult
{
    private const DATE_STRING_PATTERN = '%s-%s-%s';

    /**
     * Return processed date as a DateTime object.
     */
    public function getDateProcessedObject(): ?DateTimeInterface
    {
        $value = $this->data['dateProcessed'];

        if (
            \is_string($value) === true &&
            \strlen($value) === 6 &&
            \ctype_digit($value) === true
        ) {
            $stringDate = \sprintf(
                self::DATE_STRING_PATTERN,
                \substr($value, 4, 2),
                \substr($value, 2, 2),
                \substr($value, 0, 2)
            );

            return new DateTime($stringDate);
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
            'directEntryUserId',
            'mnemonicOfFinancialInstitution',
            'mnemonicOfSendingMember',
            'reelSequenceNumber',
        ];
    }
}
