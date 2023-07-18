<?php

declare(strict_types=1);

use EonX\EasyWebhook\Signers\Rs256Signer;

return [
    'event' => [
        'enabled' => true,
        'event_header' => 'X-Webhook-Event',
    ],
    'id' => [
        'enabled' => true,
        'id_header' => 'X-Webhook-Id',
    ],
    'method' => 'POST',
    'send_async' => true,
    'signature' => [
        'enabled' => false,
        'signer' => Rs256Signer::class,
        'signature_header' => 'X-Webhook-Signature',
        'secret' => 'easy-webhook-secret',
    ],
    'use_default_middleware' => true,
];
