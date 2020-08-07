<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Interfaces;

use Aws\Sqs\SqsClient;

interface SqsClientFactoryInterface
{
    public function create(): SqsClient;
}
