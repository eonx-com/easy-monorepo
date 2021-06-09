<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger;

use Symfony\Component\Messenger\Stamp\StampInterface;

final class BatchItemRequiresApprovalStamp implements StampInterface
{
    // No body needed.
}
