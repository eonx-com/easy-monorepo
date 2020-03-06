<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

interface EventDispatcherInterface
{
    public function dispatch(EasyAsyncEventInterface $event): EasyAsyncEventInterface;
}
