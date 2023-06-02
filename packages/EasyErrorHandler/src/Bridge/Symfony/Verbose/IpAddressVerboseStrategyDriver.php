<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Verbose;

use EonX\EasyErrorHandler\Verbose\AbstractVerboseStrategyDriver;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

final class IpAddressVerboseStrategyDriver extends AbstractVerboseStrategyDriver
{
    /**
     * @param string[] $ipAddresses
     */
    public function __construct(
        private readonly array $ipAddresses,
        ?int $priority = null
    ) {
        parent::__construct($priority);
    }

    public function isVerbose(Throwable $throwable, ?Request $request = null): ?bool
    {
        if ($request === null || $request->getClientIp() === null) {
            return null;
        }

        return IpUtils::checkIp($request->getClientIp(), $this->ipAddresses);
    }
}
