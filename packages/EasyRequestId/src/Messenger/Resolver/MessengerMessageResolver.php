<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Messenger\Resolver;

use EonX\EasyRequestId\Common\Resolver\ResolverInterface;
use EonX\EasyRequestId\Common\ValueObject\RequestIdInfo;
use EonX\EasyRequestId\Messenger\Stamp\RequestIdStamp;
use Symfony\Component\Messenger\Envelope;

final class MessengerMessageResolver implements ResolverInterface
{
    public function __construct(
        private Envelope $envelope,
    ) {
    }

    public function __invoke(): RequestIdInfo
    {
        $stamp = $this->envelope->last(RequestIdStamp::class);

        if ($stamp instanceof RequestIdStamp === false) {
            return new RequestIdInfo();
        }

        return new RequestIdInfo($stamp->getCorrelationId(), $stamp->getRequestId());
    }
}
