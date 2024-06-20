<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Generation\Bpay\Generator;

use EonX\EasyBankFiles\Generation\Bpay\ValueObject\Header;
use EonX\EasyBankFiles\Generation\Bpay\ValueObject\Trailer;
use EonX\EasyBankFiles\Generation\Bpay\ValueObject\Transaction;
use EonX\EasyBankFiles\Generation\Common\Exception\InvalidArgumentException;
use EonX\EasyBankFiles\Generation\Common\Generator\AbstractGenerator;

final class BpayGenerator extends AbstractGenerator
{
    /**
     * Generator constructor.
     *
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\InvalidArgumentException
     */
    public function __construct(
        private Header $header,
        private array $transactions,
        private ?Trailer $trailer = null,
    ) {
        if (\count($transactions) === 0) {
            throw new InvalidArgumentException('No transactions provided.');
        }
    }

    /**
     * Generate.
     *
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\LengthMismatchesException
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\ValidationFailedException
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\InvalidArgumentException
     */
    protected function generate(): void
    {
        $objects = [$this->header];
        $totalAmount = 0;

        // Ensure transactions is always an array
        $transactions = $this->transactions;

        foreach ($transactions as $transaction) {
            if (($transaction instanceof Transaction) === false) {
                throw new InvalidArgumentException(\sprintf(
                    'Transaction must be %s, %s given.',
                    Transaction::class,
                    \gettype($transaction)
                ));
            }

            $objects[] = $transaction;
            $totalAmount += (int)$transaction->getAmount();
        }

        $objects[] = $this->trailer ?? $this->createTrailer(\count($objects) - 1, $totalAmount);

        $this->writeLinesForObjects($objects);
    }

    /**
     * Return the defined line length of a generator.
     */
    protected function getLineLength(): int
    {
        return 144;
    }

    /**
     * Create new trailer.
     */
    private function createTrailer(int $count, int $totalAmount): Trailer
    {
        return new Trailer([
            'totalFileValue' => $totalAmount,
            'totalNumberOfPayments' => $count,
        ]);
    }
}
