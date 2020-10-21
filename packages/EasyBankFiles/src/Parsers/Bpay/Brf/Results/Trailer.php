<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\Bpay\Brf\Results;

use EonX\EasyBankFiles\Parsers\BaseResult;
use EonX\EasyBankFiles\Parsers\Bpay\Brf\Exceptions\InvalidSignFieldException;
use EonX\EasyBankFiles\Parsers\Bpay\Brf\SignedFieldsTrait;

/**
 * @method string|null getBillerCode()
 * @method string|null getFiller()
 */
final class Trailer extends BaseResult
{
    use SignedFieldsTrait;

    /**
     * Get the amount of error correction and type
     *
     * @return mixed[]
     *
     * @throws \EonX\EasyBankFiles\Parsers\Bpay\Brf\Exceptions\InvalidSignFieldException
     */
    public function getAmountOfErrorCorrections(): array
    {
        return $this->getTrailerAmount('amountOfErrorCorrections');
    }

    /**
     * Get the amount of payment and type
     *
     * @return mixed[]
     *
     * @throws \EonX\EasyBankFiles\Parsers\Bpay\Brf\Exceptions\InvalidSignFieldException
     */
    public function getAmountOfPayments(): array
    {
        return $this->getTrailerAmount('amountOfPayments');
    }

    /**
     * Get the amount fo reversal and type
     *
     * @return mixed[]
     *
     * @throws \EonX\EasyBankFiles\Parsers\Bpay\Brf\Exceptions\InvalidSignFieldException
     */
    public function getAmountOfReversals(): array
    {
        return $this->getTrailerAmount('amountOfReversals');
    }

    /**
     * Get number of error corrections and type
     *
     * @return mixed[]
     *
     * @throws \EonX\EasyBankFiles\Parsers\Bpay\Brf\Exceptions\InvalidSignFieldException
     */
    public function getNumberOfErrorCorrections(): array
    {
        return $this->getTrailerAmount('numberOfErrorCorrections');
    }

    /**
     * Get number of payments and type
     *
     * @return mixed[]
     *
     * @throws \EonX\EasyBankFiles\Parsers\Bpay\Brf\Exceptions\InvalidSignFieldException
     */
    public function getNumberOfPayments(): array
    {
        return $this->getTrailerAmount('numberOfPayments');
    }

    /**
     * Get Number of Reversals and type
     *
     * @return mixed[]
     *
     * @throws \EonX\EasyBankFiles\Parsers\Bpay\Brf\Exceptions\InvalidSignFieldException
     */
    public function getNumberOfReversals(): array
    {
        return $this->getTrailerAmount('numberOfReversals');
    }

    /**
     * Get the settlement amount and type
     *
     * @return mixed[]
     *
     * @throws \EonX\EasyBankFiles\Parsers\Bpay\Brf\Exceptions\InvalidSignFieldException
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
            'billerCode',
            'numberOfPayments',
            'amountOfPayments',
            'numberOfErrorCorrections',
            'amountOfErrorCorrections',
            'numberOfReversals',
            'amountOfReversals',
            'settlementAmount',
            'filler',
        ];
    }

    /**
     * Get the trailer amount and convert to proper value based on signed field
     *
     * @return mixed[]
     *
     * @throws \EonX\EasyBankFiles\Parsers\Bpay\Brf\Exceptions\InvalidSignFieldException
     */
    private function getTrailerAmount(string $attrAmount): array
    {
        $value = $this->data[$attrAmount];

        // code is in the last digit
        $sfCode = $value[\strlen($value) - 1] ?? '';
        $sfValue = $this->getSignedFieldValue($sfCode);

        if ($sfValue === null) {
            throw new InvalidSignFieldException(\sprintf('Invalid signed amount: %s', $attrAmount));
        }

        $amountOfPayments = \substr($value, 0, -1) . $sfValue['value'];

        // cents only for lengths equal to 15
        $cents = \substr($amountOfPayments, 13, 2);
        $amount = \substr($amountOfPayments, 0, 13);

        // \strlen($cents ?: '') used as \is_string($cents) makes phpstan unhappy
        $amount = (\strlen($cents ?: '') === 2) ? (int)$amount . '.' . $cents : (int)$amount;

        return [
            'amount' => $amount,
            'type' => $sfValue['type'],
        ];
    }
}
