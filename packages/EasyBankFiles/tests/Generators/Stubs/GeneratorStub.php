<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Generators\Stubs;

use EonX\EasyBankFiles\Generators\BaseGenerator;

final class GeneratorStub extends BaseGenerator
{
    /**
     * @var mixed[]
     */
    private $descriptiveRecord;

    /**
     * @var mixed[]
     */
    private $transactions;

    /**
     * StubGenerator constructor.
     *
     * @param mixed[] $descriptiveRecord
     * @param mixed[] $transactions
     *
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\ValidationFailedException
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\InvalidArgumentException
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\LengthMismatchesException
     */
    public function __construct(array $descriptiveRecord, ?array $transactions = null)
    {
        $this->descriptiveRecord = $descriptiveRecord;
        $this->transactions = $transactions ?? [];

        $this->generate();
    }

    /**
     * Generate.
     *
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\ValidationFailedException
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\InvalidArgumentException
     * @throws \EonX\EasyBankFiles\Generators\Exceptions\LengthMismatchesException
     */
    protected function generate(): void
    {
        $this->writeLinesForObjects($this->transactions);
        /** @var \EonX\EasyBankFiles\Generators\Aba\Objects\DescriptiveRecord $descriptiveRecord */
        $descriptiveRecord = $this->descriptiveRecord;
        $this->validateAttributes($descriptiveRecord, []);
    }

    /**
     * Return the defined line length of a generators.
     */
    protected function getLineLength(): int
    {
        return 120;
    }
}
