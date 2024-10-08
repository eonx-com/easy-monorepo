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
    'use_default_middleware' => true,
];
