<?php
declare(strict_types=1);

use EonX\EasyWebhook\Common\Factory\HttpClientFactory;
use EonX\EasyWebhook\Common\Signer\Rs256WebhookSigner;

return [
    'event' => [
        'enabled' => true,
        'header' => 'X-Webhook-Event',
    ],
    'id' => [
        'enabled' => true,
        'header' => 'X-Webhook-Id',
    ],
    'method' => 'POST',
    'send_async' => true,
    'signature' => [
        'enabled' => false,
        'secret' => 'easy-webhook-secret',
        'header' => 'X-Webhook-Signature',
        'signer' => Rs256WebhookSigner::class,
    ],
    'ssrf_protection' => [
        'enabled' => true,

        /**
         * Additional CIDR ranges to reject on top of the private + reserved defaults.
         */
        'extra_blocked_ranges' => [],

        /**
         * CIDR ranges to unblock by REMOVING a matching entry from the default block list (e.g.
         * "127.0.0.0/8" to reach IPv4 localhost). Each entry must match a default range verbatim
         * and must not be covered by another default (e.g. "::1/128" is inside "::/96"), otherwise
         * it is rejected at startup. To reach hosts this cannot express, use "enabled" => false.
         */
        'allowed_ranges' => [],
    ],
    'request_limits' => [
        'enabled' => false,
        'timeout' => HttpClientFactory::DEFAULT_TIMEOUT,
        'max_duration' => HttpClientFactory::DEFAULT_MAX_DURATION,
        'max_response_bytes' => HttpClientFactory::DEFAULT_MAX_RESPONSE_BYTES,
    ],
    'use_default_middleware' => true,
];
