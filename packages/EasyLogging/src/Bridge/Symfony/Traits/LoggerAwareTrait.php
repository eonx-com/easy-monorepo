<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Bridge\Symfony\Traits;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait LoggerAwareTrait
{
    protected LoggerInterface $logger;

    #[Required]
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
