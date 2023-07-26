<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Doctrine\Exceptions;

use EonX\EasyAsync\Exceptions\AbstractEasyAsyncException;
use EonX\EasyAsync\Interfaces\ShouldKillWorkerExceptionInterface;

final class DoctrineManagerClosedException extends AbstractEasyAsyncException implements
    ShouldKillWorkerExceptionInterface
{
    // No body needed
}
