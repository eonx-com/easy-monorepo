<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Generation\Aba\Generator;

use EonX\EasyBankFiles\Generation\Aba\ValueObject\DescriptiveRecord;
use EonX\EasyBankFiles\Generation\Aba\ValueObject\FileTotalRecord;
use EonX\EasyBankFiles\Generation\Aba\ValueObject\Transaction;
use EonX\EasyBankFiles\Generation\Common\Exception\InvalidArgumentException;
use EonX\EasyBankFiles\Generation\Common\Generator\AbstractGenerator;

final class AbaGenerator extends AbstractGenerator
{
    /**
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\InvalidArgumentException
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
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\LengthMismatchesException
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\ValidationFailedException
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\InvalidArgumentException
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
