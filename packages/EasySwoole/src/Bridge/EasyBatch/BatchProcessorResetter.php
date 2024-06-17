<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\EasyBatch;

use EonX\EasyBatch\Common\Processor\BatchProcessor;
use EonX\EasySwoole\AppStateResetters\AbstractAppStateResetter;

final class BatchProcessorResetter extends AbstractAppStateResetter
{
    public function __construct(
        private readonly BatchProcessor $batchProcessor,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function resetState(): void
    {
        $this->batchProcessor->reset();
    }
}
