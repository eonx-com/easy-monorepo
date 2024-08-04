<?php
declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Stub\Generation\Common\Generator;

use EonX\EasyBankFiles\Generation\Common\Generator\AbstractGenerator;

final class GeneratorStub extends AbstractGenerator
{
    private readonly array $transactions;

    /**
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\ValidationFailedException
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\InvalidArgumentException
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\LengthMismatchesException
     */
    public function __construct(
        private array $descriptiveRecord,
        ?array $transactions = null,
    ) {
        $this->transactions = $transactions ?? [];

        $this->generate();
    }

    /**
     * Generate.
     *
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\ValidationFailedException
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\InvalidArgumentException
     * @throws \EonX\EasyBankFiles\Generation\Common\Exception\LengthMismatchesException
     */
    protected function generate(): void
    {
        $this->writeLinesForObjects($this->transactions);
        /** @var \EonX\EasyBankFiles\Generation\Aba\ValueObject\DescriptiveRecord $descriptiveRecord */
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
