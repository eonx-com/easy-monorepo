<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Symfony\Messenger;

use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
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
            RequestIdServiceInterface::KEY_RESOLVED_CORRELATION_ID => $stamp->getCorrelationId(),
            RequestIdServiceInterface::KEY_RESOLVED_REQUEST_ID => $stamp->getRequestId(),
        ];
    }
}
