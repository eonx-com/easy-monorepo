<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Messenger\Resolver;

use EonX\EasyRequestId\Common\RequestId\RequestIdInterface;
use EonX\EasyRequestId\Messenger\Stamp\RequestIdStamp;
use Symfony\Component\Messenger\Envelope;

final class MessengerMessageResolver
{
    public function __construct(
        private Envelope $envelope,
    ) {
    }

    /**
     * @return null[]|string[]
     */
    public function __invoke(): array
    {
        $stamp = $this->envelope->last(RequestIdStamp::class);

        if ($stamp instanceof RequestIdStamp === false) {
            return [];
        }

        return [
            RequestIdInterface::KEY_RESOLVED_CORRELATION_ID => $stamp->getCorrelationId(),
            RequestIdInterface::KEY_RESOLVED_REQUEST_ID => $stamp->getRequestId(),
        ];
    }
}
