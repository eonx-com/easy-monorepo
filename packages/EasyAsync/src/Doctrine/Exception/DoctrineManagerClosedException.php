<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Doctrine\Exception;

use EonX\EasyAsync\Common\Exception\AbstractEasyAsyncException;
use EonX\EasyAsync\Common\Exception\ShouldKillWorkerExceptionInterface;

final class DoctrineManagerClosedException extends AbstractEasyAsyncException implements
    ShouldKillWorkerExceptionInterface
{
    // No body needed
}
