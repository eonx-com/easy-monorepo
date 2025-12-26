<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsing\Brf\ValueObject;

use EonX\EasyBankFiles\Parsing\Brf\Exception\InvalidSignFieldException;
use EonX\EasyBankFiles\Parsing\Common\ValueObject\AbstractResult;

/**
 * @method string getBillerCode()
 * @method string getFiller()
 */
final class TrailerRecord extends AbstractResult
{
    use SignedFieldsTrait;

    /**
     * Get the amount of error correction and type.
     *
     * @throws \EonX\EasyBankFiles\Parsing\Brf\Exception\InvalidSignFieldException
     */
    public function getAmountOfErrorCorrections(): array
    {
        return $this->getTrailerAmount('amountOfErrorCorrections');
    }

    /**
     * Get the amount of payment and type.
     *
     * @throws \EonX\EasyBankFiles\Parsing\Brf\Exception\InvalidSignFieldException
     */
    public function getAmountOfPayments(): array
    {
        return $this->getTrailerAmount('amountOfPayments');
    }

    /**
     * Get the amount fo reversal and type.
     *
     * @throws \EonX\EasyBankFiles\Parsing\Brf\Exception\InvalidSignFieldException
     */
    public function getAmountOfReversals(): array
    {
        return $this->getTrailerAmount('amountOfReversals');
    }

    /**
     * Get number of error corrections and type.
     *
     * @throws \EonX\EasyBankFiles\Parsing\Brf\Exception\InvalidSignFieldException
     */
    public function getNumberOfErrorCorrections(): array
    {
        return $this->getTrailerAmount('numberOfErrorCorrections');
    }

    /**
     * Get number of payments and type.
     *
     * @throws \EonX\EasyBankFiles\Parsing\Brf\Exception\InvalidSignFieldException
     */
    public function getNumberOfPayments(): array
    {
        return $this->getTrailerAmount('numberOfPayments');
    }

    /**
     * Get Number of Reversals and type.
     *
     * @throws \EonX\EasyBankFiles\Parsing\Brf\Exception\InvalidSignFieldException
     */
    public function getNumberOfReversals(): array
    {
        return $this->getTrailerAmount('numberOfReversals');
    }

    /**
     * Get the settlement amount and type.
     *
     * @throws \EonX\EasyBankFiles\Parsing\Brf\Exception\InvalidSignFieldException
     */
    public function getSettlementAmount(): array
    {
        return $this->getTrailerAmount('settlementAmount');
    }

    /**
     * Return object attributes.
     *
     * @return string[]
     */
    protected function initAttributes(): array
    {
        return [
            'amountOfErrorCorrections',
            'amountOfPayments',
            'amountOfReversals',
            'billerCode',
            'filler',
            'numberOfErrorCorrections',
            'numberOfPayments',
            'numberOfReversals',
            'settlementAmount',
        ];
    }

    /**
     * Get the trailer amount and convert to proper value based on signed field.
     *
     * @throws \EonX\EasyBankFiles\Parsing\Brf\Exception\InvalidSignFieldException
     */
    private function getTrailerAmount(string $attrAmount): array
    {
        $value = $this->data[$attrAmount];

        // Code is in the last digit
        $sfCode = $value[\strlen((string)$value) - 1] ?? '';
        $sfValue = $this->getSignedFieldValue($sfCode);

        if ($sfValue === null) {
            throw new InvalidSignFieldException(\sprintf('Invalid signed amount: %s', $attrAmount));
        }

        $amountOfPayments = \substr((string)$value, 0, -1) . $sfValue['value'];

        $amount = \ltrim($amountOfPayments, '0');

        return [
            'amount' => $amount === '' ? '0' : $amount,
            'type' => $sfValue['type'],
        ];
    }
}
