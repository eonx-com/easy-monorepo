<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Laravel\Console\Commands\Lumen;

use EonX\EasyErrorHandler\Interfaces\ErrorCodesGroupProcessorInterface;
use Illuminate\Console\Command;

final class AnalyzeErrorCodesCommand extends Command
{
    public function __construct()
    {
        $this->signature = 'easy-error-handler:error-codes:analyze';
        $this->description = 'Analyzes existing error codes';

        parent::__construct();
    }

    public function handle(ErrorCodesGroupProcessorInterface $errorCodesGroupProcessor): void
    {
        $errorCodesDto = $errorCodesGroupProcessor->process();

        if ($errorCodesDto->hasErrorCodes() === false) {
            $this->info('No error code found.');

            return;
        }

        $this->table(
            ['Error code group', 'Next error code to use'],
            $errorCodesDto->getNextGroupedErrorCodes()
        );

        $this->newLine();
        $this->info(\sprintf(
            'The error code for the new group is %s.',
            $errorCodesDto->getNextGroupErrorCode()
        ));
        $this->newLine();
    }
}
