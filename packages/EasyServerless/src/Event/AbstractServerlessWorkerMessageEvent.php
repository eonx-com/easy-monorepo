<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Event;

use Symfony\Component\Messenger\Event\AbstractWorkerMessageEvent as SymfonyAbstractWorkerMessageEvent;

abstract class AbstractServerlessWorkerMessageEvent extends SymfonyAbstractWorkerMessageEvent
{
}
