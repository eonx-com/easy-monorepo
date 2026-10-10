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

        /**
         * Hostnames whose requests bypass the SSRF check entirely, for a legitimate private target
         * such as an internal load balancer published in public DNS with private addresses. Entries
         * are "host" (any port) or "host:port", matched case-insensitively against the webhook URL;
         * no scheme, path or wildcard, and an IP literal in a URL never matches. Redirects from an
         * allowed host are never followed. All other hosts stay fully protected. Entries are trimmed
         * and empty ones dropped, so an empty env var means an empty list.
         */
        'allowed_hosts' => [],
    ],
    'request_limits' => [
        'enabled' => false,
        'timeout' => HttpClientFactory::DEFAULT_TIMEOUT,
        'max_duration' => HttpClientFactory::DEFAULT_MAX_DURATION,
        'max_response_bytes' => HttpClientFactory::DEFAULT_MAX_RESPONSE_BYTES,
    ],
    'use_default_middleware' => true,
];
