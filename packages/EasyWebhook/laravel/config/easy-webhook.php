<?php
declare(strict_types=1);

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
        'timeout' => 10,
        'max_duration' => 30,
        'max_response_bytes' => 1048576,
    ],
    'use_default_middleware' => true,
];
