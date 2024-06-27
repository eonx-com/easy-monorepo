<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Exception;

use EonX\EasyBatch\Common\Exception\EasyBatchPreventProcessExceptionInterface as PreventProcessInterface;

final class BatchCancelledException extends AbstractEasyBatchException implements PreventProcessInterface
{
    // No body needed
}
