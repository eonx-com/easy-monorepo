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
    'request_limits' => [
        'enabled' => false,
        'timeout' => HttpClientFactory::DEFAULT_TIMEOUT,
        'max_duration' => HttpClientFactory::DEFAULT_MAX_DURATION,
        'max_response_bytes' => HttpClientFactory::DEFAULT_MAX_RESPONSE_BYTES,
    ],
    'use_default_middleware' => true,
];
