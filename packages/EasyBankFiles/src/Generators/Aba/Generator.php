<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Generators\Aba;

use EonX\EasyBankFiles\Generators\Aba\Objects\DescriptiveRecord;
use EonX\EasyBankFiles\Generators\Aba\Objects\FileTotalRecord;
use EonX\EasyBankFiles\Generators\Aba\Objects\Transaction;
use EonX\EasyBankFiles\Generators\BaseGenerator;
use EonX\EasyBankFiles\Generators\Exceptions\InvalidArgumentException;

final class Generator extends BaseGenerator
{
    /**
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\InvalidArgumentException
     */
    public function __construct(
        private DescriptiveRecord $descriptiveRecord,
        private array $transactions,
        private ?FileTotalRecord $fileTotalRecord = null,
    ) {
        if (\count($transactions) === 0) {
            throw new InvalidArgumentException('No transactions provided.');
        }
    }

    /**
     * Generate content.
     *
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\LengthMismatchesException
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\ValidationFailedException
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\InvalidArgumentException
     */
    protected function generate(): void
    {
        $objects = [$this->descriptiveRecord];

        $creditTotal = 0;
        $debitTotal = 0;

        foreach ($this->transactions as $transaction) {
            if (($transaction instanceof Transaction) === false) {
                throw new InvalidArgumentException(\sprintf(
                    'Transaction must be %s, %s given.',
                    Transaction::class,
                    \gettype($transaction)
                ));
            }

            $objects[] = $transaction;

            if ((int)$transaction->getTransactionCode() === Transaction::CODE_GENERAL_CREDIT) {
                $creditTotal += (int)$transaction->getAmount();
            }
            if ((int)$transaction->getTransactionCode() === Transaction::CODE_GENERAL_DEBIT) {
                $debitTotal += (int)$transaction->getAmount();
            }
        }

        $objects[] = $this->createFileTotalRecord(\count($objects) - 1, $creditTotal, $debitTotal);

        $this->writeLinesForObjects($objects);
    }

    /**
     * Return the defined line length of a generator.
     */
    protected function getLineLength(): int
    {
        return 120;
    }

    /**
     * Create new file total record.
     */
    private function createFileTotalRecord(int $count, int $creditTotal, int $debitTotal): FileTotalRecord
    {
        if ($this->fileTotalRecord !== null) {
            return $this->fileTotalRecord;
        }

        return $this->fileTotalRecord = new FileTotalRecord([
            'fileUserCountOfRecordsType' => $count,
            'fileUserCreditTotalAmount' => $creditTotal,
            'fileUserDebitTotalAmount' => $debitTotal,
            'fileUserNetTotalAmount' => \abs($creditTotal - $debitTotal),
        ]);
    }
}
