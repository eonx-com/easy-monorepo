<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results;

use EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch\Header;
use EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch\Trailer;
use EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch\TransactionTypePayment;
use EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch\TransactionTypeRefusal;
use EonX\EasyBankFiles\Parsers\DirectEntryBatch\Results\Batch\TransactionTypeReturn;

final class Batch
{
    private Header $header;

    private Trailer $trailer;

    /**
     * @var array<mixed>
     */
    private array $transactions = [];

    public function addTransaction(
        TransactionTypePayment|TransactionTypeReturn|TransactionTypeRefusal $transaction
    ): self {
        $this->transactions[] = $transaction;

        return $this;
    }

    public function getHeader(): Header
    {
        return $this->header;
    }

    public function getTrailer(): Trailer
    {
        return $this->trailer;
    }

    /**
     * @return array<mixed>
     */
    public function getTransactions(): array
    {
        return $this->transactions;
    }

    public function hasTrailer(): bool
    {
        return isset($this->trailer) === true;
    }

    public function hasTransaction(): bool
    {
        return \count($this->transactions) > 0;
    }

    public function setHeader(Header $header): self
    {
        $this->header = $header;

        return $this;
    }

    public function setTrailer(Trailer $trailer): self
    {
        $this->trailer = $trailer;

        return $this;
    }
}
