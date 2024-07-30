<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Common\Provider;

use EonX\EasyRequestId\Common\Resolver\ResolverInterface;

interface RequestIdProviderInterface
{
    public function getCorrelationId(): string;

    public function getCorrelationIdHeaderName(): string;

    public function getRequestId(): string;

    public function getRequestIdHeaderName(): string;

    /**
     * @param \EonX\EasyRequestId\Common\Resolver\ResolverInterface|callable():\EonX\EasyRequestId\Common\ValueObject\RequestIdInfo $resolver
     */
    public function setResolver(ResolverInterface|callable $resolver): self;
}
