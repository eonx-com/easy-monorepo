<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Bridge\Symfony\Traits;

use Psr\Log\LoggerInterface;

trait LoggerAwareTrait
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @required
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
