<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Events;

use EonX\EasySecurity\Interfaces\SecurityContextInterface;

final class SecurityContextCreatedEvent
{
    /**
     * @var \EonX\EasySecurity\Interfaces\SecurityContextInterface
     */
    private $securityContext;

    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function getSecurityContext(): SecurityContextInterface
    {
        return $this->securityContext;
    }
}
